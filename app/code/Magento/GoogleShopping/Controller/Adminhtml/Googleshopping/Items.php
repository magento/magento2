<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping;

use Magento\Backend\App\Action;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * GoogleShopping Admin Items Controller
 */
abstract class Items extends \Magento\Backend\App\Action
{
    /**
     * @var NotifierInterface
     */
    protected $notifier;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @param Action\Context $context
     * @param NotifierInterface $notifier
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     */
    public function __construct(
        Action\Context $context,
        NotifierInterface $notifier,
        \Magento\Framework\Url\EncoderInterface $urlEncoder
    ) {
        parent::__construct($context);
        $this->urlEncoder = $urlEncoder;
        $this->notifier = $notifier;
    }

    /**
     * Retrieve synchronization process mutex
     *
     * @return \Magento\GoogleShopping\Model\Flag
     */
    protected function _getFlag()
    {
        return $this->_objectManager->get('Magento\GoogleShopping\Model\Flag')->loadSelf();
    }

    /**
     * Redirect user to Google Captcha challenge
     *
     * @param \Zend_Gdata_App_CaptchaRequiredException $e
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function _redirectToCaptcha($e)
    {
        $redirectUrl = $this->getUrl(
            '*/*/index',
            [
                'store' => $this->_getStore()->getId(),
                'captcha_token' => $this->urlEncoder->encode($e->getCaptchaToken()),
                'captcha_url' => $this->urlEncoder->encode($e->getCaptchaUrl())
            ]
        );
        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            return $resultJson->setData(['redirect' => $redirectUrl]);
        } else {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl($redirectUrl);
        }
    }

    /**
     * Get store object, basing on request
     *
     * @return \Magento\Store\Model\Store
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _getStore()
    {
        $store = $this->_objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore(
            (int)$this->getRequest()->getParam('store', 0)
        );
        if (!$store || 0 == $store->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to select a Store View'));
        }
        return $store;
    }

    /**
     * Check access to this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_GoogleShopping::items');
    }
}
