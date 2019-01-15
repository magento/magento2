<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 100.0.2
 */
class SelectAttributes extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->coreRegistry = $registry;
    }

    /**
     * Get Add new Attribute button
     *
     * @param string $dataProvider
     * @return string
     */
    public function getAddNewAttributeButton($dataProvider = '')
    {
        /** @var \Magento\Backend\Block\Widget\Button $attributeCreate */
        $attributeCreate = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        if ($attributeCreate->getAuthorization()->isAllowed('Magento_Catalog::attributes_attributes')) {
            $attributeCreate->setDataAttribute(
                [
                    'mage-init' => [
                        'productAttributes' => [
                            'dataProvider' => $dataProvider,
                            'url' => $this->getUrl('catalog/product_attribute/new', [
                                'store' => $this->coreRegistry->registry('current_product')->getStoreId(),
                                'product_tab' => 'variations',
                                'popup' => 1,
                                '_query' => [
                                    'attribute' => [
                                        'is_global' => 1,
                                        'frontend_input' => 'select',
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ]
            )->setType(
                'button'
            )->setLabel(
                __('Create New Attribute')
            );
            return $attributeCreate->toHtml();
        } else {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Select Attributes');
    }
}
