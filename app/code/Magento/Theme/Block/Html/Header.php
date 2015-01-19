<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html;

use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Context;

/**
 * Html page header block
 */
class Header extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'html/header.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CustomerViewHelper $customerViewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerViewHelper $customerViewHelper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_customerViewHelper = $customerViewHelper;
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve welcome text
     *
     * @return string
     */
    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
                $customerName = $this->_customerViewHelper->getCustomerName(
                    $this->_customerSession->getCustomerDataObject()
                );
                $this->_data['welcome'] = __(
                    'Welcome, %1!',
                    $this->escapeHtml($customerName)
                );
            } else {
                $this->_data['welcome'] = $this->_scopeConfig->getValue(
                    'design/header/welcome',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }
        }
        return $this->_data['welcome'];
    }
}
