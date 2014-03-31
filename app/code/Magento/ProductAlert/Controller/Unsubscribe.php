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

/**
 * ProductAlert unsubscribe controller
 *
 * @category   Magento
 * @package    Magento_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
use Magento\App\Action\NotFoundException;
use Magento\App\RequestInterface;

class Unsubscribe extends \Magento\App\Action\Action
{
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
    public function priceAction()
    {
        $productId = (int)$this->getRequest()->getParam('product');

        if (!$productId) {
            $this->_redirect('');
            return;
        }
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            /* @var $product \Magento\Catalog\Model\Product */
            $this->messageManager->addError(__('We can\'t find the product.'));
            $this->_redirect('customer/account/');
            return;
        }

        try {
            $model = $this->_objectManager->create(
                'Magento\ProductAlert\Model\Price'
            )->setCustomerId(
                $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomerId()
            )->setProductId(
                $product->getId()
            )->setWebsiteId(
                $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            )->loadByParam();
            if ($model->getId()) {
                $model->delete();
            }

            $this->messageManager->addSuccess(__('You deleted the alert subscription.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->getResponse()->setRedirect($product->getProductUrl());
    }

    /**
     * @return void
     */
    public function priceAllAction()
    {
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        /* @var $session \Magento\Customer\Model\Session */

        try {
            $this->_objectManager->create(
                'Magento\ProductAlert\Model\Price'
            )->deleteCustomer(
                $session->getCustomerId(),
                $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            );
            $this->messageManager->addSuccess(__('You will no longer receive price alerts for this product.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->_redirect('customer/account/');
    }

    /**
     * @return void
     */
    public function stockAction()
    {
        $productId = (int)$this->getRequest()->getParam('product');

        if (!$productId) {
            $this->_redirect('');
            return;
        }

        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        /* @var $product \Magento\Catalog\Model\Product */
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->messageManager->addError(__('The product was not found.'));
            $this->_redirect('customer/account/');
            return;
        }

        try {
            $model = $this->_objectManager->create(
                'Magento\ProductAlert\Model\Stock'
            )->setCustomerId(
                $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomerId()
            )->setProductId(
                $product->getId()
            )->setWebsiteId(
                $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            )->loadByParam();
            if ($model->getId()) {
                $model->delete();
            }
            $this->messageManager->addSuccess(__('You will no longer receive stock alerts for this product.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->getResponse()->setRedirect($product->getProductUrl());
    }

    /**
     * @return void
     */
    public function stockAllAction()
    {
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        /* @var $session \Magento\Customer\Model\Session */

        try {
            $this->_objectManager->create(
                'Magento\ProductAlert\Model\Stock'
            )->deleteCustomer(
                $session->getCustomerId(),
                $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            );
            $this->messageManager->addSuccess(__('You will no longer receive stock alerts.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->_redirect('customer/account/');
    }
}
