<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\Component;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Ui\Component\Container;

/**
 * Class Attributes
 *
 * @api
 * @since 101.0.0
 */
class Attributes extends AbstractModifier
{
    const GROUP_SORT_ORDER = 15;
    const GROUP_NAME = 'Attributes';
    const GROUP_CODE = 'attributes';

    /**
     * @var UrlInterface
     * @since 101.0.0
     */
    protected $urlBuilder;

    /**
     * @var Registry
     * @since 101.0.0
     */
    protected $registry;

    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var AuthorizationInterface
     * @since 101.0.0
     */
    protected $authorization;

    /**
     * @param UrlInterface $urlBuilder
     * @param Registry $registry
     * @param AuthorizationInterface $authorization
     * @param LocatorInterface $locator
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Registry $registry,
        AuthorizationInterface $authorization,
        LocatorInterface $locator
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registry = $registry;
        $this->authorization = $authorization;
        $this->locator = $locator;
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
     * Check if can add attributes on product form.
     *
     * @return boolean
     */
    private function canAddAttributes()
    {
        $isWrapped = $this->registry->registry('use_wrapper');
        if (!isset($isWrapped)) {
            $isWrapped = true;
        }

        return $isWrapped && $this->authorization->isAllowed('Magento_Catalog::attributes_attributes');
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->canAddAttributes()) {
            return $meta;
        }

        if (isset($meta[static::GROUP_CODE])) {
            $meta[static::GROUP_CODE]['arguments']['data']['config']['component'] =
                'Magento_Catalog/js/components/attributes-fieldset';
        }

        $meta = $this->customizeAddAttributeModal($meta);
        $meta = $this->customizeCreateAttributeModal($meta);
        $meta = $this->customizeAttributesGrid($meta);

        return $meta;
    }

    /**
     * Modify meta customize attribute modal.
     *
     * @param array $meta
     * @return array
     */
    private function customizeAddAttributeModal(array $meta)
    {
        $meta['add_attribute_modal']['arguments']['data']['config'] = [
            'isTemplate' => false,
            'componentType' => Component\Modal::NAME,
            'dataScope' => '',
            'provider' => 'product_form.product_form_data_source',
            'imports' => [
                'state' => '!index=product_attribute_add_form:responseStatus'
            ],
            'options' => [
                'title' => __('Add Attribute'),
                'buttons' => [
                    [
                        'text' => 'Cancel',
                        'actions' => [
                            [
                                'targetName' => '${ $.name }',
                                'actionName' => 'actionCancel'
                            ]
                        ]
                    ],
                    [
                        'text' => __('Add Selected'),
                        'class' => 'action-primary',
                        'actions' => [
                            [
                                'targetName' => '${ $.name }.product_attributes_grid',
                                'actionName' => 'save'
                            ],
                            [
                                'closeModal'
                            ]
                        ]
                    ]
                ],
            ],
        ];

        $meta['add_attribute_modal']['children'] = [
            'add_new_attribute_button' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'admin__field-complex-attributes',
                            'formElement' => Container::NAME,
                            'componentType' => Container::NAME,
                            'content' => __('Select Attribute'),
                            'label' => false,
                            'template' => 'ui/form/components/complex',
                        ],
                    ],
                ],
                'children' => [
                    'add_new_attribute_button' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Container::NAME,
                                    'componentType' => Container::NAME,
                                    'component' => 'Magento_Ui/js/form/components/button',
                                    'additionalClasses' => '',
                                    'actions' => [
                                        [
                                            'targetName' => 'product_form.product_form.add_attribute_modal'
                                                . '.create_new_attribute_modal',
                                            'actionName' => 'toggleModal',
                                        ],
                                        [
                                            'targetName' => 'product_form.product_form.add_attribute_modal'
                                                . '.create_new_attribute_modal.product_attribute_add_form',
                                            'actionName' => 'destroyInserted'
                                        ],
                                        [
                                            'targetName'
                                            => 'product_form.product_form.add_attribute_modal'
                                                . '.create_new_attribute_modal.product_attribute_add_form',
                                            'actionName' => 'render'
                                        ]
                                    ],
                                    'title' => __('Create New Attribute'),
                                    'provider' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return $meta;
    }

    /**
     * Modify meta to customize create attribute modal.
     *
     * @param array $meta
     * @return array
     */
    private function customizeCreateAttributeModal(array $meta)
    {
        $params = [
            'group' => static::GROUP_CODE,
            'groupName' => self::GROUP_NAME,
            'groupSortOrder' => self::GROUP_SORT_ORDER,
            'store' => $this->locator->getStore()->getId(),
            'product' => $this->locator->getProduct()->getId(),
            'type' => $this->locator->getProduct()->getTypeId(),
            'set' => $this->locator->getProduct()->getAttributeSetId(),
            'message_key' => 'messages',
            'popup' => 1
        ];

        $meta['add_attribute_modal']['children']['create_new_attribute_modal'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'isTemplate' => false,
                        'componentType' => Component\Modal::NAME,
                        'dataScope' => 'data.new_attribute',
                        'provider' => 'product_form.product_form_data_source',
                        'options' => [
                            'title' => __('New Attribute')
                        ],
                        'imports' => [
                            'state' => '!index=product_attribute_add_form:responseStatus'
                        ],
                    ]
                ]
            ],
            'children' => [
                'product_attribute_add_form' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('New Attribute'),
                                'componentType' => Component\Container::NAME,
                                'component' => 'Magento_Catalog/js/components/new-attribute-insert-form',
                                'dataScope' => '',
                                'update_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'render_url' => $this->urlBuilder->getUrl(
                                    'mui/index/render_handle',
                                    [
                                        'handle' => 'catalog_product_attribute_edit_form',
                                        'buttons' => 1
                                    ]
                                ),
                                'autoRender' => false,
                                'ns' => 'product_attribute_add_form',
                                'externalProvider' => 'product_attribute_add_form'
                                    . '.product_attribute_add_form_data_source',
                                'toolbarContainer' => '${ $.parentName }',
                                'formSubmitType' => 'ajax',
                                'saveUrl' => $this->urlBuilder->getUrl('catalog/product_attribute/save', $params),
                                'validateUrl' => $this->urlBuilder->getUrl(
                                    'catalog/product_attribute/validate',
                                    $params
                                ),
                                'productId' => $this->locator->getProduct()->getId(),
                                'productType' => $this->locator->getProduct()->getTypeId(),
                                'imports' => [
                                    'attributeSetId' => '${ $.provider }:data.product.attribute_set_id',
                                ],
                                'exports' => [
                                    'saveUrl' => '${ $.externalProvider }:client.urls.save',
                                    'validateUrl' => '${ $.externalProvider }:client.urls.beforeSave',
                                    'attributeSetId' => '${ $.externalProvider }:params.set',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $meta;
    }

    /**
     * Modify meta to customize attribute grid.
     *
     * @param array $meta
     * @return array
     */
    private function customizeAttributesGrid(array $meta)
    {
        $meta['add_attribute_modal']['children']['product_attributes_grid'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magento_Catalog/js/components/attributes-insert-listing',
                        'componentType' => Component\Container::NAME,
                        'autoRender' => false,
                        'dataScope' => 'product_attributes_grid',
                        'externalProvider' => 'product_attributes_grid.product_attributes_grid_data_source',
                        'selectionsProvider' => '${ $.ns }.${ $.ns }.product_attributes_columns.ids',
                        'ns' => 'product_attributes_grid',
                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                        'immediateUpdateBySelection' => true,
                        'behaviourType' => 'edit',
                        'externalFilterMode' => true,
                        'dataLinks' => ['imports' => false, 'exports' => false],
                        'formProvider' => 'ns = ${ $.namespace }, index = product_form',
                        'groupCode' => static::GROUP_CODE,
                        'groupName' => static::GROUP_NAME,
                        'groupSortOrder' => static::GROUP_SORT_ORDER,
                        'addAttributeUrl' =>
                            $this->urlBuilder->getUrl('catalog/product/addAttributeToTemplate'),
                        'productId' => $this->locator->getProduct()->getId(),
                        'productType' => $this->locator->getProduct()->getTypeId(),
                        'loading' => false,
                        'imports' => [
                            'attributeSetId' => '${ $.provider }:data.product.attribute_set_id'
                        ],
                        'exports' => [
                            'attributeSetId' => '${ $.externalProvider }:params.template_id'
                        ]
                    ],
                ],
            ]
        ];
        return $meta;
    }
}
