<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;

/**
 * Renderer for sendemail checkbox
 */
class Sendemail extends Element
{
    /**
     * @var string
     */
    protected $_template = 'edit/tab/account/form/renderer/sendemail.phtml';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    protected $_storeManager = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * Check if Single Store Mode is enabled
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Get form HTML ID
     * @return string
     */
    public function getFormHtmlId()
    {
        return $this->getForm()->getHtmlIdPrefix();
    }
}
