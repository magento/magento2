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
namespace Magento\Customer\Block\Account;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;

class Customer extends \Magento\View\Element\Template
{
    /** @var CustomerAccountServiceInterface */
    protected $_customerAccountService;

    /** @var \Magento\Customer\Helper\View */
    protected $_viewHelper;

    /**
     * @var \Magento\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Service\V1\CustomerCurrentService
     */
    protected $currentCustomer;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Customer\Helper\View $viewHelper
     * @param \Magento\App\Http\Context $httpContext
     * @param \Magento\Customer\Service\V1\CustomerCurrentService $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Helper\View $viewHelper,
        \Magento\App\Http\Context $httpContext,
        \Magento\Customer\Service\V1\CustomerCurrentService $currentCustomer,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_customerAccountService = $customerAccountService;
        $this->_viewHelper = $viewHelper;
        $this->httpContext = $httpContext;
        $this->currentCustomer = $currentCustomer;
        $this->_isScopePrivate = true;
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH);
    }

    /**
     * Return the full name of the customer currently logged in
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        try {
            $customer = $this->_customerAccountService->getCustomer($this->currentCustomer->getCustomerId());
            return $this->escapeHtml($this->_viewHelper->getCustomerName($customer));
        } catch (\Magento\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
