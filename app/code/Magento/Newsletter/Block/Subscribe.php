<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter subscribe block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block;

class Subscribe extends \Magento\Framework\View\Element\Template
{
    /**
     * Newsletter session
     *
     * @var \Magento\Newsletter\Model\Session
     */
    protected $_newsletterSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Newsletter\Model\Session $newsletterSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Newsletter\Model\Session $newsletterSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_newsletterSession = $newsletterSession;
        $this->_isScopePrivate = true;
    }

    /**
     * Get success message
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->_newsletterSession->getSuccess();
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_newsletterSession->getError();
    }

    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}
