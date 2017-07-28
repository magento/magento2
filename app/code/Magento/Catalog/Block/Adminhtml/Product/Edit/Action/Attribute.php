<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml catalog product action attribute update
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action;

use Magento\Catalog\Helper\Product\Edit\Action\Attribute as ActionAttribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * @api
 * @since 2.0.0
 */
class Attribute extends \Magento\Backend\Block\Widget
{
    /**
     * Adminhtml catalog product edit action attribute
     *
     * @var ActionAttribute
     * @since 2.0.0
     */
    protected $_helperActionAttribute = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ActionAttribute $helperActionAttribute
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ActionAttribute $helperActionAttribute,
        array $data = []
    ) {
        $this->_helperActionAttribute = $helperActionAttribute;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                    'catalog/product/',
                    ['store' => $this->getRequest()->getParam('store', 0)]
                ) . '\')',
                'class' => 'back'
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Reset'),
                'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/*', ['_current' => true]) . '\')',
                'class' => 'reset'
            ]
        );

        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#attributes-edit-form']],
                ]
            ]
        );
    }

    /**
     * Retrieve selected products for update
     *
     * @return Collection
     * @since 2.0.0
     */
    public function getProducts()
    {
        return $this->_getHelper()->getProducts();
    }

    /**
     * Retrieve block attributes update helper
     *
     * @return ActionAttribute|null
     * @since 2.0.0
     */
    protected function _getHelper()
    {
        return $this->_helperActionAttribute;
    }

    /**
     * Retrieve back button html code
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve cancel button html code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve save button html code
     *
     * @return string
     * @since 2.0.0
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Get save url
     *
     * @return string
     * @since 2.0.0
     */
    public function getSaveUrl()
    {
        $helper = $this->_helperActionAttribute;
        return $this->getUrl('*/*/save', ['store' => $helper->getSelectedStoreId()]);
    }

    /**
     * Get validation url
     *
     * @return string
     * @since 2.0.0
     */
    public function getValidationUrl()
    {
        return $this->getUrl('catalog/*/validate', ['_current' => true]);
    }
}
