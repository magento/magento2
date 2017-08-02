<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute;

/**
 * Product attribute edit page
 *
 * @api
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block group name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_blockGroup = 'Magento_Catalog';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_product_attribute';

        parent::_construct();

        if ($this->getRequest()->getParam('popup')) {
            $this->buttonList->remove('back');
            if ($this->getRequest()->getParam('product_tab') != 'variations') {
                $this->addButton(
                    'save_in_new_set',
                    [
                        'label' => __('Save in New Attribute Set'),
                        'class' => 'save',
                        'onclick' => 'saveAttributeInNewSet(\'' . __('Enter Name for New Attribute Set') . '\')'
                    ],
                    100
                );
            }
        } else {
            $this->addButton(
                'save_and_edit_button',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ]
            );
        }

        $this->buttonList->update('save', 'label', __('Save Attribute'));
        $this->buttonList->update('save', 'class', 'save primary');
        $this->buttonList->update(
            'save',
            'data_attribute',
            ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]
        );

        $entityAttribute = $this->_coreRegistry->registry('entity_attribute');
        if (!$entityAttribute || !$entityAttribute->getIsUserDefined()) {
            $this->buttonList->remove('delete');
        } else {
            $this->buttonList->update('delete', 'label', __('Delete Attribute'));
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }

    /**
     * Retrieve header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('entity_attribute')->getId()) {
            $frontendLabel = $this->_coreRegistry->registry('entity_attribute')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return __('Edit Product Attribute "%1"', $this->escapeHtml($frontendLabel));
        }
        return __('New Product Attribute');
    }

    /**
     * Retrieve URL for validation
     *
     * @return string
     * @since 2.0.0
     */
    public function getValidationUrl()
    {
        return $this->getUrl('catalog/*/validate', ['_current' => true]);
    }

    /**
     * Retrieve URL for save
     *
     * @return string
     * @since 2.0.0
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'catalog/product_attribute/save',
            ['_current' => true, 'back' => null, 'product_tab' => $this->getRequest()->getParam('product_tab')]
        );
    }
}
