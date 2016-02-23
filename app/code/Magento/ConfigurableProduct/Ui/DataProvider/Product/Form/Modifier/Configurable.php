<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\Product\Type;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;

/**
 * Data provider for Configurable products
 */
class Configurable extends AbstractModifier
{
    const GROUP_CONFIGURABLE = 'configurable';

    /**
     * @var array
     */
    private static $availableProductTypes = [
        ConfigurableType::TYPE_CODE,
        Type::TYPE_SIMPLE,
        Type::TYPE_VIRTUAL
    ];

    /**
     * @var string
     */
    private static $groupContent = 'content';

    /**
     * @var int
     */
    private static $sortOrder = 30;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(
        LocatorInterface $locator
    ) {
        $this->locator = $locator;
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
        //return $meta;

        if (in_array($this->locator->getProduct()->getTypeId(), self::$availableProductTypes)) {
            $meta = array_merge_recursive(
                $meta,
                [
                    static::GROUP_CONFIGURABLE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Configurations'),
                                    'collapsible' => true,
                                    'opened' => true,
                                    'componentType' => Form\Fieldset::NAME,
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $meta,
                                        self::$groupContent,
                                        self::$sortOrder
                                    ),
                                ],
                            ],
                        ],
                        'children' => $this->getPanelChildren(),
                    ],
                ]
            );
        }

        return $meta;
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren() {
        return [
            'configurable_products_button_set' => $this->getButtonSet(),
        ];
    }

    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => __(
                            'Configurable products allow customers to choose options '
                            . '(Ex: shirt color). You need to create a simple product for each '
                            . 'configuration (Ex: a product for each color).'
                        ),
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'create_configurable_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' =>
                                            'product_form.product_form.configurableModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' =>
                                            'product_form.product_form.configurableModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Create Configurations'),
                            ],
                        ],
                    ],
                ],
                //'variations' => $this->getGrid(),
            ],
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getGrid()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => __('Current Variations'),
                        'renderDefaultRecord' => true,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'addButton' => false,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data.links',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => 'grouped_product_listing',
                        'map' => [
                            'id' => 'entity_id',
                            'name' => 'name',
                        ],
                        'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                        'sortOrder' => 20,
                        'columnsHeader' => true,
                        'columnsHeaderAfterRender' => false,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     */
    protected function getRows()
    {
        return [
            'record' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'container',
                            'isTemplate' => false,
                            'is_collection' => true,
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'dataScope' => '',
                        ],
                    ],
                ],
                'children' => [
                    'id' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'componentType' => Form\Field::NAME,
                                    'dataScope' => 'qty',
                                    'label' => __('Default Quantity'),
                                    'fit' => true,
                                    'additionalClasses' => 'admin__field-small',
                                    'sortOrder' => 80,
                                ],
                            ],
                        ],
                    ],
                    'actionDelete' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'data-grid-actions-cell',
                                    'componentType' => 'actionDelete',
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'label' => __('Actions'),
                                    'sortOrder' => 90,
                                    'fit' => true,
                                ],
                            ],
                        ],
                    ],
                    'position' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'componentType' => Form\Field::NAME,
                                    'dataScope' => 'position',
                                    'sortOrder' => 100,
                                    'visible' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}