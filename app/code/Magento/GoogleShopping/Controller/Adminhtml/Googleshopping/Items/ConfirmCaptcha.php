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
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

class ConfirmCaptcha extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
{
    /**
     * Confirm CAPTCHA
     *
     * @return void
     */
    public function execute()
    {

        $storeId = $this->_getStore()->getId();
        try {
            $this->_objectManager->create(
                'Magento\GoogleShopping\Model\Service'
            )->getClient(
                $storeId,
                $this->_objectManager->get(
                    'Magento\Core\Helper\Data'
                )->urlDecode(
                    $this->getRequest()->getParam('captcha_token')
                ),
                $this->getRequest()->getParam('user_confirm')
            );
            $this->messageManager->addSuccess(__('Captcha has been confirmed.'));
        } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
            $this->messageManager->addError(__('There was a Captcha confirmation error: %1', $e->getMessage()));
            $this->_redirectToCaptcha($e);
            return;
        } catch (\Zend_Gdata_App_Exception $e) {
            $this->messageManager->addError(
                $this->_objectManager->get(
                    'Magento\GoogleShopping\Helper\Data'
                )->parseGdataExceptionMessage(
                    $e->getMessage()
                )
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError(__('Something went wrong during Captcha confirmation.'));
        }

        $this->_redirect('adminhtml/*/index', array('store' => $storeId));
    }
}
