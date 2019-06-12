<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Customer\Model\Customer\Source\GroupSourceInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Module\ModuleManagerInterface as ModuleManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Modal;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class AdvancedPricing
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 101.0.0
 */
class AdvancedPricing extends AbstractModifier
{
    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var ModuleManager
     * @since 101.0.0
     */
    protected $moduleManager;

    /**
     * @var GroupManagementInterface
     * @since 101.0.0
     */
    protected $groupManagement;

    /**
     * @var SearchCriteriaBuilder
     * @since 101.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     * @since 101.0.0
     */
    protected $groupRepository;

    /**
     * @var Data
     * @since 101.0.0
     */
    protected $directoryHelper;

    /**
     * @var StoreManagerInterface
     * @since 101.0.0
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @var string
     * @since 101.0.0
     */
    protected $scopeName;

    /**
     * @var array
     * @since 101.0.0
     */
    protected $meta = [];

    /**
     * @var GroupSourceInterface
     */
    private $customerGroupSource;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupManagementInterface $groupManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleManager $moduleManager
     * @param Data $directoryHelper
     * @param ArrayManager $arrayManager
     * @param string $scopeName
     * @param GroupSourceInterface $customerGroupSource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleManager $moduleManager,
        Data $directoryHelper,
        ArrayManager $arrayManager,
        $scopeName = '',
        GroupSourceInterface $customerGroupSource = null
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->groupRepository = $groupRepository;
        $this->groupManagement = $groupManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleManager = $moduleManager;
        $this->directoryHelper = $directoryHelper;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
        $this->customerGroupSource = $customerGroupSource
            ?: ObjectManager::getInstance()->get(GroupSourceInterface::class);
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->specialPriceDataToInline();
        $this->customizeTierPrice();

        if (isset($this->meta['advanced-pricing'])) {
            $this->addAdvancedPriceLink();
            $this->customizeAdvancedPricing();
        }

        return $this->meta;
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Prepare price fields
     *
     * Add currency symbol and validation
     *
     * @param string $fieldCode
     * @return $this
     * @since 101.0.0
     */
    protected function preparePriceFields($fieldCode)
    {
        $pricePath = $this->arrayManager->findPath($fieldCode, $this->meta, null, 'children');

        if ($pricePath) {
            $this->meta = $this->arrayManager->set(
                $pricePath . '/arguments/data/config/addbefore',
                $this->meta,
                $this->getStore()->getBaseCurrency()->getCurrencySymbol()
            );
            $this->meta = $this->arrayManager->merge(
                $pricePath . '/arguments/data/config',
                $this->meta,
                ['validation' => ['validate-zero-or-greater' => true]]
            );
        }

        return $this;
    }

    /**
     * Customize tier price field
     *
     * @return $this
     */
    private function customizeTierPrice()
    {
        $tierPricePath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_TIER_PRICE,
            $this->meta,
            null,
            'children'
        );

        if ($tierPricePath) {
            $this->meta = $this->arrayManager->merge(
                $tierPricePath,
                $this->meta,
                $this->getTierPriceStructure($tierPricePath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($tierPricePath, 0, -3)
                . '/' . ProductAttributeInterface::CODE_TIER_PRICE,
                $this->meta,
                $this->arrayManager->get($tierPricePath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($tierPricePath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    private function getCustomerGroups()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }

        return $this->customerGroupSource->toOptionArray();
    }

    /**
     * Check tier_price attribute scope is global
     *
     * @return bool
     */
    private function isScopeGlobal()
    {
        return $this->locator->getProduct()
            ->getResource()
            ->getAttribute(ProductAttributeInterface::CODE_TIER_PRICE)
            ->isScopeGlobal();
    }

    /**
     * Get websites list
     *
     * @return array
     */
    private function getWebsites()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $product = $this->locator->getProduct();

        if (!$this->isScopeGlobal() && $product->getStoreId()) {
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->getStore()->getWebsite();

            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        } elseif (!$this->isScopeGlobal()) {
            $websitesList = $this->storeManager->getWebsites();
            $productWebsiteIds = $product->getWebsiteIds();
            foreach ($websitesList as $website) {
                /** @var \Magento\Store\Model\Website $website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $websites[] = [
                    'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                    'value' => $website->getId(),
                ];
            }
        }

        return $websites;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    private function getDefaultCustomerGroup()
    {
        return $this->groupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     * @since 101.0.0
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    private function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    private function isMultiWebsites()
    {
        return !$this->storeManager->isSingleStoreMode();
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    private function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->locator->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }

    /**
     * Add link to open Advanced Pricing Panel
     *
     * @return $this
     */
    private function addAdvancedPriceLink()
    {
        $pricePath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_PRICE,
            $this->meta,
            null,
            'children'
        );

        if ($pricePath) {
            $this->meta = $this->arrayManager->merge(
                $pricePath . '/arguments/data/config',
                $this->meta,
                ['additionalClasses' => 'admin__field-small']
            );

            $advancedPricingButton['arguments']['data']['config'] = [
                'dataScope' => 'advanced_pricing_button',
                'displayAsLink' => true,
                'formElement' => Container::NAME,
                'componentType' => Container::NAME,
                'component' => 'Magento_Ui/js/form/components/button',
                'template' => 'ui/form/components/button/container',
                'imports' => [
                    'childError' => $this->scopeName . '.advanced_pricing_modal.advanced-pricing:error',
                ],
                'actions' => [
                    [
                        'targetName' => $this->scopeName . '.advanced_pricing_modal',
                        'actionName' => 'toggleModal',
                    ]
                ],
                'title' => __('Advanced Pricing'),
                'additionalForGroup' => true,
                'provider' => false,
                'source' => 'product_details',
                'sortOrder' =>
                    $this->arrayManager->get($pricePath . '/arguments/data/config/sortOrder', $this->meta) + 1,
            ];

            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($pricePath, 0, -1) . '/advanced_pricing_button',
                $this->meta,
                $advancedPricingButton
            );
        }

        return $this;
    }

    /**
     * Get tier price dynamic rows structure
     *
     * @param string $tierPricePath
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getTierPriceStructure($tierPricePath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-tier-price',
                        'label' => __('Customer Group Price'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' =>
                            $this->arrayManager->get($tierPricePath . '/arguments/data/config/disabled', $this->meta),
                        'required' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($tierPricePath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'website_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'website_id',
                                        'label' => __('Website'),
                                        'options' => $this->getWebsites(),
                                        'value' => $this->getDefaultWebsite(),
                                        'visible' => $this->isMultiWebsites(),
                                        'disabled' => ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()),
                                        'sortOrder' => 10,
                                    ],
                                ],
                            ],
                        ],
                        'cust_group' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'cust_group',
                                        'label' => __('Customer Group'),
                                        'options' => $this->getCustomerGroups(),
                                        'value' => $this->getDefaultCustomerGroup(),
                                        'sortOrder' => 20,
                                    ],
                                ],
                            ],
                        ],
                        'price_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Quantity'),
                                        'dataScope' => 'price_qty',
                                        'sortOrder' => 30,
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-greater-than-zero' => true,
                                            'validate-digits' => false,
                                            'validate-number' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'price' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Price'),
                                        'enableLabel' => true,
                                        'dataScope' => 'price',
                                        'addbefore' => $this->locator->getStore()
                                                                     ->getBaseCurrency()
                                                                     ->getCurrencySymbol(),
                                        'sortOrder' => 40,
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-greater-than-zero' => true,
                                            'validate-number' => true,
                                        ],
                                        'imports' => [
                                            'priceValue' => '${ $.provider }:data.product.price',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                        'sortOrder' => 50,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Special price data move to inline group
     *
     * @return $this
     */
    private function specialPriceDataToInline()
    {
        $pathFrom = $this->arrayManager->findPath('special_from_date', $this->meta, null, 'children');
        $pathTo = $this->arrayManager->findPath('special_to_date', $this->meta, null, 'children');

        if ($pathFrom && $pathTo) {
            $this->meta = $this->arrayManager->merge(
                $this->arrayManager->slicePath($pathFrom, 0, -2) . '/arguments/data/config',
                $this->meta,
                [
                    'label' => __('Special Price From'),
                    'additionalClasses' => 'admin__control-grouped-date',
                    'breakLine' => false,
                    'component' => 'Magento_Ui/js/form/components/group',
                    'scopeLabel' =>
                        $this->arrayManager->get($pathFrom . '/arguments/data/config/scopeLabel', $this->meta),
                ]
            );
            $this->meta = $this->arrayManager->merge(
                $pathFrom . '/arguments/data/config',
                $this->meta,
                [
                    'label' => __('Special Price From'),
                    'scopeLabel' => null,
                    'additionalClasses' => 'admin__field-date'
                ]
            );
            $this->meta = $this->arrayManager->merge(
                $pathTo . '/arguments/data/config',
                $this->meta,
                [
                    'label' => __('To'),
                    'scopeLabel' => null,
                    'additionalClasses' => 'admin__field-date'
                ]
            );
            // Move special_to_date to special_from_date container
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($pathFrom, 0, -1) . '/special_to_date',
                $this->meta,
                $this->arrayManager->get(
                    $pathTo,
                    $this->meta
                )
            );
            $this->meta = $this->arrayManager->remove($this->arrayManager->slicePath($pathTo, 0, -2), $this->meta);
        }

        return $this;
    }

    /**
     * Customize Advanced Pricing Panel
     *
     * @return $this
     */
    private function customizeAdvancedPricing()
    {
        $this->meta['advanced-pricing']['arguments']['data']['config']['opened'] = true;
        $this->meta['advanced-pricing']['arguments']['data']['config']['collapsible'] = false;
        $this->meta['advanced-pricing']['arguments']['data']['config']['label'] = '';

        $this->meta['advanced_pricing_modal']['arguments']['data']['config'] = [
            'isTemplate' => false,
            'componentType' => Modal::NAME,
            'dataScope' => '',
            'provider' => 'product_form.product_form_data_source',
            'onCancel' => 'actionDone',
            'options' => [
                'title' => __('Advanced Pricing'),
                'buttons' => [
                    [
                        'text' => __('Done'),
                        'class' => 'action-primary',
                        'actions' => [
                            [
                                'targetName' => '${ $.name }',
                                'actionName' => 'actionDone'
                            ]
                        ]
                    ],
                ],
            ],
        ];

        $this->meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . ProductAttributeInterface::CODE_PRICE,
                $this->meta,
                null,
                'children'
            ),
            $this->meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'Magento_Ui/js/form/components/group',
                        ],
                    ],
                ],
            ]
        );

        $this->meta['advanced_pricing_modal']['children']['advanced-pricing'] = $this->meta['advanced-pricing'];
        unset($this->meta['advanced-pricing']);

        return $this;
    }

    /**
     * Retrieve store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    private function getStore()
    {
        return $this->locator->getStore();
    }
}
