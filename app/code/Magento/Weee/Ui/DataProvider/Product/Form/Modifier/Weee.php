<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Manager\Website as WebsiteManager;

/**
 * Class Weee
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Weee extends AbstractModifier
{
    const FORM_ELEMENT_WEEE = 'weee';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var SourceCountry
     */
    protected $sourceCountry;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var EavAttributeFactory
     */
    protected $eavAttributeFactory;

    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var array
     */
    private $countries;

    /**
     * @var array
     */
    private $regions;

    /**
     * @param LocatorInterface $locator
     * @param SourceCountry $sourceCountry
     * @param DirectoryHelper $directoryHelper
     * @param EavAttributeFactory $eavAttributeFactory
     * @param WebsiteManager $websiteManager
     */
    public function __construct(
        LocatorInterface $locator,
        SourceCountry $sourceCountry,
        DirectoryHelper $directoryHelper,
        EavAttributeFactory $eavAttributeFactory,
        WebsiteManager $websiteManager
    ) {
        $this->locator = $locator;
        $this->sourceCountry = $sourceCountry;
        $this->directoryHelper = $directoryHelper;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        foreach ($meta as $groupCode => $groupConfig) {
            $meta[$groupCode] = $this->modifyMetaConfig($groupConfig);
        }

        return $meta;
    }

    /**
     * Modification of group config
     *
     * @param array $metaConfig
     * @return array
     */
    protected function modifyMetaConfig(array $metaConfig)
    {
        if (isset($metaConfig['children'])) {
            foreach ($metaConfig['children'] as $attributeCode => $attributeConfig) {
                if ($this->startsWith($attributeCode, self::CONTAINER_PREFIX)) {
                    $metaConfig['children'][$attributeCode] = $this->modifyMetaConfig($attributeConfig);
                } elseif (
                    !empty($attributeConfig['arguments']['data']['config']['formElement']) &&
                    $attributeConfig['arguments']['data']['config']['formElement'] === static::FORM_ELEMENT_WEEE
                ) {
                    $metaConfig['children'][$attributeCode] =
                        $this->modifyAttributeConfig($attributeCode, $attributeConfig);
                }
            }
        }

        return $metaConfig;
    }

    /**
     * Modification of attribute config
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function modifyAttributeConfig($attributeCode, array $attributeConfig)
    {
        /** @var Product $product */
        $product = $this->locator->getProduct();
        /** @var EavAttribute $eavAttribute */
        $eavAttribute = $this->eavAttributeFactory->create()->loadByCode(Product::ENTITY, $attributeCode);

        return array_replace_recursive($attributeConfig, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'formElement' => 'component',
                        'renderDefaultRecord' => false,
                        'itemTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
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
                        'countryState' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Container::NAME,
                                        'component' => 'Magento_Weee/js/fpt-group',
                                        'visible' => true,
                                        'label' => __('Country/State'),
                                    ],
                                ],
                            ],
                            'children' => [
                                'country' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'dataType' => Text::NAME,
                                                'formElement' => Select::NAME,
                                                'componentType' => Field::NAME,
                                                'dataScope' => 'country',
                                                'visible' => true,
                                                'options' => $this->getCountries(),
                                                'validation' => [
                                                    'required-entry' => true,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'state' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Field::NAME,
                                                'dataType' => Text::NAME,
                                                'formElement' => Select::NAME,
                                                'component' => 'Magento_Weee/js/regions-tax-select',
                                                'dataScope' => 'state',
                                                'options' => $this->getRegions(),
                                                'filterBy' => [
                                                    'field' => 'country'
                                                ],
                                                'caption' => '*',
                                                'visible' => true,
                                            ],
                                        ],
                                    ],
                                ],
                                'val' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Field::NAME,
                                                'formElement' => Input::NAME,
                                                'dataType' => Text::NAME,
                                                'enableLabel' => false,
                                                'visible' => true,
                                                'additionalClasses' => 'weee_hidden',
                                                'validation' => [
                                                    'validate-fpt-group' => true
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'value' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Tax'),
                                        'enableLabel' => true,
                                        'dataScope' => 'value',
                                        'validation' => [
                                            'required-entry' => true
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'website_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'website_id',
                                        'label' => __('Website'),
                                        'visible' => $this->websiteManager->isMultiWebsites(),
                                        'options' => $this->websiteManager->getWebsites($product, $eavAttribute),
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
                                        'label' => __('Action'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Retrieve countries
     *
     * @return array|null
     */
    protected function getCountries()
    {
        if (null === $this->countries) {
            $this->countries = $this->sourceCountry->toOptionArray();
        }

        return $this->countries;
    }

    /**
     * Retrieve regions
     *
     * @return array
     */
    protected function getRegions()
    {
        if (null === $this->regions) {
            $regions = $this->directoryHelper->getRegionData();
            $this->regions = [];

            unset($regions['config']);

            foreach ($regions as $countryCode => $countryRegions) {
                foreach ($countryRegions as $regionId => $regionData) {
                    $this->regions[] = [
                        'label' => $regionData['name'],
                        'value' => $regionId,
                        'country' => $countryCode,
                    ];
                }
            }
        }

        return $this->regions;
    }
}
