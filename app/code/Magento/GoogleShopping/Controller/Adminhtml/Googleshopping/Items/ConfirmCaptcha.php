<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

use Magento\Backend\App\Action;
use Magento\Framework\Notification\NotifierInterface;

class ConfirmCaptcha extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @param Action\Context $context
     * @param NotifierInterface $notifier
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     */
    public function __construct(
        Action\Context $context,
        NotifierInterface $notifier,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        parent::__construct($context, $notifier, $urlEncoder);
        $this->urlDecoder = $urlDecoder;
    }

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
                $this->urlDecoder->decode(
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
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__('Something went wrong during Captcha confirmation.'));
        }

        $this->_redirect('adminhtml/*/index', ['store' => $storeId]);
    }
}
