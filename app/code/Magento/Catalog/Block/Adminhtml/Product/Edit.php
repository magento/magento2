<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer edit block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product;

class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_attributeSetFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Product $productHelper,
        array $data = []
    ) {
        $this->_productHelper = $productHelper;
        $this->_attributeSetFactory = $attributeSetFactory;
        $this->_coreRegistry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_edit');
        $this->setUseContainer(true);
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Add elements in layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if (!$this->getRequest()->getParam('popup')) {
            if ($this->getToolbar()) {
                $this->getToolbar()->addChild(
                    'back_button',
                    'Magento\Backend\Block\Widget\Button',
                    [
                        'label' => __('Back'),
                        'title' => __('Back'),
                        'onclick' => 'setLocation(\'' . $this->getUrl(
                            'catalog/*/',
                            ['store' => $this->getRequest()->getParam('store', 0)]
                        ) . '\')',
                        'class' => 'action-back'
                    ]
                );
            }
        } else {
            $this->addChild(
                'back_button',
                'Magento\Backend\Block\Widget\Button',
                ['label' => __('Close Window'), 'onclick' => 'window.close()', 'class' => 'cancel']
            );
        }

        if (!$this->getProduct()->isReadonly()) {
            $this->addChild(
                'reset_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Reset'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/*', ['_current' => true]) . '\')'
                ]
            );
        }

        if (!$this->getProduct()->isReadonly() && $this->getToolbar()) {
            $this->getToolbar()->addChild(
                'save-split-button',
                'Magento\Backend\Block\Widget\Button\SplitButton',
                [
                    'id' => 'save-split-button',
                    'label' => __('Save'),
                    'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                    'button_class' => 'widget-button-save',
                    'options' => $this->_getSaveSplitButtonOptions()
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * @return string
     */
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * @return string
     */
    public function getSaveAndEditButtonHtml()
    {
        return $this->getChildHtml('save_and_edit_button');
    }

    /**
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Get Save Split Button html
     *
     * @return string
     */
    public function getSaveSplitButtonHtml()
    {
        return $this->getChildHtml('save-split-button');
    }

    /**
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('catalog/*/validate', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('catalog/*/save', ['_current' => true, 'back' => null]);
    }

    /**
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'catalog/*/save',
            ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}', 'active_tab' => null]
        );
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return mixed
     */
    public function getProductSetId()
    {
        $setId = false;
        if (!($setId = $this->getProduct()->getAttributeSetId()) && $this->getRequest()) {
            $setId = $this->getRequest()->getParam('set', null);
        }
        return $setId;
    }

    /**
     * @return string
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl('catalog/*/duplicate', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        if ($this->getProduct()->getId()) {
            $header = $this->escapeHtml($this->getProduct()->getName());
        } else {
            $header = __('New Product');
        }
        return $header;
    }

    /**
     * @return string
     */
    public function getAttributeSetName()
    {
        if ($setId = $this->getProduct()->getAttributeSetId()) {
            $set = $this->_attributeSetFactory->create()->load($setId);
            return $set->getAttributeSetName();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }

    /**
     * Get fields masks from config
     *
     * @return array
     */
    public function getFieldsAutogenerationMasks()
    {
        return $this->_productHelper->getFieldsAutogenerationMasks();
    }

    /**
     * Retrieve available placeholders
     *
     * @return array
     */
    public function getAttributesAllowedForAutogeneration()
    {
        return $this->_productHelper->getAttributesAllowedForAutogeneration();
    }

    /**
     * Get data for JS (product type transition)
     *
     * @return string
     */
    public function getTypeSwitcherData()
    {
        return $this->jsonEncoder->encode(
            [
                'tab_id' => 'product_info_tabs_downloadable_items',
                'is_virtual_id' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight::VIRTUAL_FIELD_HTML_ID,
                'weight_id' => 'weight',
                'current_type' => $this->getProduct()->getTypeId(),
                'attributes' => $this->_getAttributes(),
            ]
        );
    }

    /**
     * Get formed array with attribute codes and Apply To property
     *
     * @return array
     */
    protected function _getAttributes()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->getProduct();
        $attributes = [];

        foreach ($product->getAttributes() as $key => $attribute) {
            $attributes[$key] = $attribute->getApplyTo();
        }
        return $attributes;
    }

    /**
     * Get dropdown options for save split button
     *
     * @return array
     */
    protected function _getSaveSplitButtonOptions()
    {
        $options = [];
        if (!$this->getRequest()->getParam('popup')) {
            $options[] = [
                'id' => 'edit-button',
                'label' => __('Save & Edit'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '[data-form=edit-product]'],
                    ],
                ],
                'default' => true,
            ];
        }

        $options[] = [
            'id' => 'new-button',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndNew', 'target' => '[data-form=edit-product]'],
                ],
            ],
        ];
        if (!$this->getRequest()->getParam('popup') && $this->getProduct()->isDuplicable()) {
            $options[] = [
                'id' => 'duplicate-button',
                'label' => __('Save & Duplicate'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndDuplicate', 'target' => '[data-form=edit-product]'],
                    ],
                ],
            ];
        }
        $options[] = [
            'id' => 'close-button',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save', 'target' => '[data-form=edit-product]']],
            ],
        ];
        return $options;
    }

    /**
     * Check whether new product is being created
     *
     * @return bool
     */
    protected function _isProductNew()
    {
        $product = $this->getProduct();
        return !$product || !$product->getId();
    }
}
