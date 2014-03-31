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
 * @category    Magento
 * @package     Magento_ProductAlert
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ProductAlert\Controller;

use Magento\App\Action\Context;
use Magento\App\RequestInterface;

/**
 * ProductAlert controller
 *
 * @category   Magento
 * @package    Magento_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Add extends \Magento\App\Action\Action
{
    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Check customer authentication for some actions
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_objectManager->get('Magento\Customer\Model\Session')->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
            if (!$this->_objectManager->get('Magento\Customer\Model\Session')->getBeforeUrl()) {
                $this->_objectManager->get(
                    'Magento\Customer\Model\Session'
                )->setBeforeUrl(
                    $this->_redirect->getRefererUrl()
                );
            }
        }
        return parent::dispatch($request);
    }

    /**
     * @return void
     */
    public function testObserverAction()
    {
        $object = new \Magento\Object();
        $observer = $this->_objectManager->get('Magento\ProductAlert\Model\Observer');
        $observer->process($object);
    }

    /**
     * @return void
     */
    public function priceAction()
    {
        $backUrl = $this->getRequest()->getParam(\Magento\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return;
        }

        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        if (!$product->getId()) {
            /* @var $product \Magento\Catalog\Model\Product */
            $this->messageManager->addError(__('There are not enough parameters.'));
            if ($this->_isInternal($backUrl)) {
                $this->getResponse()->setRedirect($backUrl);
            } else {
                $this->_redirect('/');
            }
            return;
        }

        try {
            $model = $this->_objectManager->create(
                'Magento\ProductAlert\Model\Price'
            )->setCustomerId(
                $this->_objectManager->get('Magento\Customer\Model\Session')->getId()
            )->setProductId(
                $product->getId()
            )->setPrice(
                $product->getFinalPrice()
            )->setWebsiteId(
                $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            );
            $model->save();
            $this->messageManager->addSuccess(__('You saved the alert subscription.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * @return void
     */
    public function stockAction()
    {
        $backUrl = $this->getRequest()->getParam(\Magento\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return;
        }

        if (!($product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId))) {
            /* @var $product \Magento\Catalog\Model\Product */
            $this->messageManager->addError(__('There are not enough parameters.'));
            $this->getResponse()->setRedirect($backUrl);
            return;
        }

        try {
            $model = $this->_objectManager->create(
                'Magento\ProductAlert\Model\Stock'
            )->setCustomerId(
                $this->_objectManager->get('Magento\Customer\Model\Session')->getId()
            )->setProductId(
                $product->getId()
            )->setWebsiteId(
                $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            );
            $model->save();
            $this->messageManager->addSuccess(__('Alert subscription has been saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Check if URL is internal
     *
     * @param string $url
     * @return bool
     */
    protected function _isInternal($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }
        $currentStore = $this->_storeManager->getStore();
        return strpos(
            $url,
            $currentStore->getBaseUrl()
        ) === 0 || strpos(
            $url,
            $currentStore->getBaseUrl(\Magento\UrlInterface::URL_TYPE_LINK, true)
        ) === 0;
    }
}
