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
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping;

use \Magento\Framework\Notification\NotifierInterface;
use \Magento\Backend\App\Action;

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
     * @param \Magento\Backend\App\Action\Context $context
     * @param NotifierInterface $notifier
     */
    public function __construct(Action\Context $context, NotifierInterface $notifier)
    {
        parent::__construct($context);
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
            array(
                'store' => $this->_getStore()->getId(),
                'captcha_token' => $this->_objectManager->get(
                    'Magento\Core\Helper\Data'
                )->urlEncode(
                    $e->getCaptchaToken()
                ),
                'captcha_url' => $this->_objectManager->get('Magento\Core\Helper\Data')->urlEncode($e->getCaptchaUrl())
            )
        );
        if ($this->getRequest()->isAjax()) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array('redirect' => $redirectUrl))
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
            'Magento\Framework\StoreManagerInterface'
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
