<?php
/**
 *
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

namespace Magento\Customer\Controller\Adminhtml\Customer;

use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\Data\AddressBuilder;
use Magento\Customer\Service\V1\Data\CustomerDetailsBuilder;

/**
 *  Class to invalidate tokens for customers
 */
class InvalidateToken extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Reset customer's tokens handler
     *
     * @return void
     */
    public function execute()
    {
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            try {
                /** @var \Magento\Integration\Service\V1\CustomerTokenService $tokenService */
                $tokenService = $this->_objectManager->get('Magento\Integration\Service\V1\CustomerTokenService');
                $tokenService->revokeCustomerAccessToken($customerId);
                $this->messageManager->addSuccess(__('You have revoked the customer\'s tokens.'));
                $this->_redirect('customer/index/edit', array('id' => $customerId, '_current' => true));
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('customer/index/edit', array('id' => $customerId, '_current' => true));
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a customer to revoke.'));
        $this->_redirect('customer/index/index');
    }
}
