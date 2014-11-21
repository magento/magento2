<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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
