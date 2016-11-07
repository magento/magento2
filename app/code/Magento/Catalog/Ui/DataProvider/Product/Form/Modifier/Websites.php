<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;

/**
 * Class Websites customizes websites panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Websites extends AbstractModifier
{
    const SORT_ORDER = 40;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var array
     */
    protected $websitesOptionsList;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $websitesList;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();

        if (!$this->storeManager->isSingleStoreMode() && $modelId) {
            $websiteIds = $this->getWebsitesValues();
            foreach ($this->getWebsitesList() as $website) {
                if (!in_array($website['id'], $websiteIds) && $website['storesCount']) {
                    $data[$modelId]['product']['copy_to_stores'][$website['id']] = [];
                    foreach ($website['groups'] as $group) {
                        foreach ($group['stores'] as $storeView) {
                            $data[$modelId]['product']['copy_to_stores'][$website['id']][] = [
                                'storeView' => $storeView['name'],
                                'copy_from' => 0,
                                'copy_to' => $storeView['id'],
                            ];
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->storeManager->isSingleStoreMode()) {
            $meta = array_replace_recursive(
                $meta,
                [
                    'websites' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'admin__fieldset-product-websites',
                                    'label' => __('Product in Websites'),
                                    'collapsible' => true,
                                    'componentType' => Form\Fieldset::NAME,
                                    'dataScope' => self::DATA_SCOPE_PRODUCT,
                                    'disabled' => false,
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $meta,
                                        'search-engine-optimization',
                                        self::SORT_ORDER
                                    )
                                ],
                            ],
                        ],
                        'children' => $this->getFieldsForFieldset(),
                    ],
                ]
            );
        }

        return $meta;
    }

    /**
     * Prepares children for the parent fieldset
     *
     * @return array
     */
    protected function getFieldsForFieldset()
    {
        $children = [];
        $websiteIds = $this->getWebsitesValues();
        $websitesList = $this->getWebsitesList();
        $isNewProduct = !$this->locator->getProduct()->getId();
        $tooltip = [
            'link' => 'http://docs.magento.com/m2/ce/user_guide/configuration/scope.html',
            'description' => __(
                'If your Magento installation has multiple websites, ' .
                'you can edit the scope to use the product on specific sites.'
            ),
        ];
        $sortOrder = 0;
        $label = __('Websites');

        $defaultWebsiteId = $this->websiteRepository->getDefault()->getId();
        foreach ($websitesList as $website) {
            $isChecked = in_array($website['id'], $websiteIds)
                || ($defaultWebsiteId == $website['id'] && $isNewProduct);
            $children[$website['id']] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'componentType' => Form\Field::NAME,
                            'formElement' => Form\Element\Checkbox::NAME,
                            'description' => __($website['name']),
                            'tooltip' => $tooltip,
                            'sortOrder' => $sortOrder,
                            'dataScope' => 'website_ids.' . $website['id'],
                            'label' => $label,
                            'valueMap' => [
                                'true' => (string)$website['id'],
                                'false' => '0',
                            ],
                            'value' => $isChecked ? (string)$website['id'] : '0',
                        ],
                    ],
                ],
            ];

            $sortOrder++;
            $tooltip = null;
            $label = ' ';

            if (!$isNewProduct && !in_array($website['id'], $websiteIds) && $website['storesCount']) {
                $children['copy_to_stores.' . $website['id']] = $this->getDynamicRow($website['id'], $sortOrder);
                $sortOrder++;
            }
        }

        return $children;
    }

    /**
     * Prepares dynamic rows configuration
     *
     * @param int $websiteId
     * @param int $sortOrder
     * @return array
     */
    protected function getDynamicRow($websiteId, $sortOrder)
    {
        $configRow = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => DynamicRows::NAME,
                        'label' => ' ',
                        'renderDefaultRecord' => true,
                        'addButton' => false,
                        'columnsHeader' => true,
                        'dndConfig' => ['enabled' => false],
                        'imports' => [
                            'visible' => '${$.namespace}.${$.namespace}.websites.' . $websiteId . ':checked'
                        ],
                        'itemTemplate' => 'record',
                        'dataScope' => '',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => $websiteId,
                            ],
                        ],
                    ],
                    'children' => [
                        'storeView' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Form\Field::NAME,
                                        'formElement' => Form\Element\Input::NAME,
                                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'dataScope' => 'storeView',
                                        'label' => __('Store View'),
                                        'fit' => true,
                                        'sortOrder' => 0,
                                    ],
                                ],
                            ],
                        ],
                        'copy_from' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'formElement' => Form\Element\Select::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'component' => 'Magento_Ui/js/form/element/ui-select',
                                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                                        'disableLabel' => true,
                                        'filterOptions' => false,
                                        'selectType' => 'optgroup',
                                        'multiple' => false,
                                        'dataScope' => 'copy_from',
                                        'label' => __('Copy Data from'),
                                        'options' => $this->getWebsitesOptions(),
                                        'sortOrder' => 1,
                                        'selectedPlaceholders' => [
                                            'defaultPlaceholder' => __('Default Values'),
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        'copy_to' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Form\Element\DataType\Number::NAME,
                                        'formElement' => Form\Element\Hidden::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'dataScope' => 'copy_to',
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
            ],
        ];
        return $configRow;
    }

    /**
     * Manage options list for selects
     *
     * @return array
     */
    protected function getWebsitesOptions()
    {
        if (!empty($this->websitesOptionsList)) {
            return $this->websitesOptionsList;
        }
        return $this->websitesOptionsList = $this->getWebsitesOptionsList();
    }

    /**
     * @return array
     */
    protected function getWebsitesOptionsList()
    {
        $options = [
            [
                'value' => '0',
                'label' => __('Default Values'),
            ],
        ];
        $websitesList = $this->getWebsitesList();
        $websiteIds = $this->getWebsitesValues();
        foreach ($websitesList as $website) {
            if (!in_array($website['id'], $websiteIds)) {
                continue;
            }
            $websiteOption = [
                'value' => '0.' . $website['id'],
                'label' => __($website['name']),
            ];
            $groupOptions = [];
            foreach ($website['groups'] as $group) {
                $groupOption = [
                    'value' => '0.' . $website['id'] . '.' . $group['id'],
                    'label' => __($group['name']),
                ];
                $storeViewOptions = [];
                foreach ($group['stores'] as $storeView) {
                    $storeViewOptions[] = [
                        'value' => $storeView['id'],
                        'label' => __($storeView['name']),
                    ];
                }
                if (!empty($storeViewOptions)) {
                    $groupOption['optgroup'] = $storeViewOptions;
                    $groupOptions[] = $groupOption;
                } else {
                    $groupOption = null;
                }
            }
            if (!empty($groupOptions)) {
                $websiteOption['optgroup'] = $groupOptions;
                $options[] = $websiteOption;
            } else {
                $websiteOption = null;
            }
        }
        return $options;
    }

    /**
     * Prepares websites list with groups and stores as array
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getWebsitesList()
    {
        if (!empty($this->websitesList)) {
            return $this->websitesList;
        }
        $this->websitesList = [];
        $groupList = $this->groupRepository->getList();
        $storesList = $this->storeRepository->getList();

        foreach ($this->websiteRepository->getList() as $website) {
            $websiteId = $website->getId();
            if (!$websiteId) {
                continue;
            }
            $websiteRow = [
                'id' => $websiteId,
                'name' => $website->getName(),
                'storesCount' => 0,
                'groups' => [],
            ];
            foreach ($groupList as $group) {
                $groupId = $group->getId();
                if (!$groupId || $group->getWebsiteId() != $websiteId) {
                    continue;
                }
                $groupRow = [
                    'id' => $groupId,
                    'name' => $group->getName(),
                    'stores' => [],
                ];
                foreach ($storesList as $store) {
                    $storeId = $store->getId();
                    if (!$storeId || $store->getStoreGroupId() != $groupId) {
                        continue;
                    }
                    $websiteRow['storesCount']++;
                    $groupRow['stores'][] = [
                        'id' => $storeId,
                        'name' => $store->getName(),
                    ];
                }
                $websiteRow['groups'][] = $groupRow;
            }
            $this->websitesList[] = $websiteRow;
        }

        return $this->websitesList;
    }

    /**
     * Return array of websites ids, assigned to the product
     *
     * @return array
     */
    protected function getWebsitesValues()
    {
        return $this->locator->getWebsiteIds();
    }
}
