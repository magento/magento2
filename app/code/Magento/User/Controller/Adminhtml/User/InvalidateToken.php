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

namespace Magento\User\Controller\Adminhtml\User;

/**
 * Class InvalidateToken - used to invalidate/revoke all authentication tokens for a specific user.
 */
class InvalidateToken extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($userId = $this->getRequest()->getParam('user_id')) {
            /** @var \Magento\Integration\Service\V1\AdminTokenService $tokenService */
            $tokenService = $this->_objectManager->get('\Magento\Integration\Service\V1\AdminTokenService');
            try {
                $tokenService->revokeAdminAccessToken($userId);
                $this->messageManager->addSuccess(__('You have revoked the user\'s tokens.'));
                $this->_redirect('adminhtml/*/edit', array('user_id' => $userId));
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', array('user_id' => $userId));
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a user to revoke.'));
        $this->_redirect('adminhtml/*');
    }
}
