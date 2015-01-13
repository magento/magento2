<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping;

use Magento\Backend\App\Action;
use Magento\Framework\Notification\NotifierInterface;

/**
 * GoogleShopping Admin Items Controller
 *
 * @name       \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Items extends \Magento\Backend\App\Action
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
     * @return void
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
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(['redirect' => $redirectUrl])
            );
        } else {
            $this->_redirect($redirectUrl);
        }
    }

    /**
     * Get store object, basing on request
     *
     * @return \Magento\Store\Model\Store
     * @throws \Magento\Framework\Model\Exception
     */
    public function _getStore()
    {
        $store = $this->_objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore(
            (int)$this->getRequest()->getParam('store', 0)
        );
        if (!$store || 0 == $store->getId()) {
            throw new \Magento\Framework\Model\Exception(__('Unable to select a Store View'));
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
