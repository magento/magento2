<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

        $this->_redirect('adminhtml/*/index', ['store' => $storeId]);
    }
}
