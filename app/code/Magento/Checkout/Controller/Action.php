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
namespace Magento\Checkout\Controller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller for onepage checkouts
 */
abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement
    ) {
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
        parent::__construct($context);
    }

    /**
     * Make sure customer is valid, if logged in
     *
     * By default will add error messages and redirect to customer edit form
     *
     * @param bool $redirect - stop dispatch and redirect?
     * @param bool $addErrors - add error messages?
     * @return bool
     */
    protected function _preDispatchValidateCustomer($redirect = true, $addErrors = true)
    {
        try {
            $customer = $this->customerRepository->getById($this->_customerSession->getCustomerId());
        } catch (NoSuchEntityException $e) {
            return true;
        }

        if (isset($customer)) {
            $validationResult = $this->accountManagement->validate($customer);
            if (!$validationResult->isValid()) {
                if ($addErrors) {
                    foreach ($validationResult->getMessages() as $error) {
                        $this->messageManager->addError($error);
                    }
                }
                if ($redirect) {
                    $this->_redirect('customer/account/edit');
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                }
                return false;
            }
        }
        return true;
    }
}
