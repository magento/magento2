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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * ProductAlert controller
 *
 * @category   Magento
 * @package    Magento_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ProductAlert\Controller;

class Add extends \Magento\Core\Controller\Front\Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_objectManager->get('Magento\Customer\Model\Session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if(!$this->_objectManager->get('Magento\Customer\Model\Session')->getBeforeUrl()) {
                $this->_objectManager->get('Magento\Customer\Model\Session')->setBeforeUrl($this->_getRefererUrl());
            }
        }
    }

    public function testObserverAction()
    {
        $object = new \Magento\Object();
        $observer = $this->_objectManager->get('Magento\ProductAlert\Model\Observer');
        $observer->process($object);
    }

    public function priceAction()
    {
        $session = $this->_objectManager->get('Magento\Catalog\Model\Session');
        $backUrl    = $this->getRequest()->getParam(\Magento\Core\Controller\Front\Action::PARAM_NAME_URL_ENCODED);
        $productId  = (int) $this->getRequest()->getParam('product_id');
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return ;
        }

        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        if (!$product->getId()) {
            /* @var $product \Magento\Catalog\Model\Product */
            $session->addError(__('There are not enough parameters.'));
            if ($this->_isUrlInternal($backUrl)) {
                $this->_redirectUrl($backUrl);
            } else {
                $this->_redirect('/');
            }
            return ;
        }

        try {
            $model = $this->_objectManager->create('Magento\ProductAlert\Model\Price')
                ->setCustomerId($this->_objectManager->get('Magento\Customer\Model\Session')->getId())
                ->setProductId($product->getId())
                ->setPrice($product->getFinalPrice())
                ->setWebsiteId(
                    $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
                );
            $model->save();
            $session->addSuccess(__('You saved the alert subscription.'));
        }
        catch (\Exception $e) {
            $session->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->_redirectReferer();
    }

    public function stockAction()
    {
        $session = $this->_objectManager->get('Magento\Catalog\Model\Session');
        /* @var $session \Magento\Catalog\Model\Session */
        $backUrl    = $this->getRequest()->getParam(\Magento\Core\Controller\Front\Action::PARAM_NAME_URL_ENCODED);
        $productId  = (int) $this->getRequest()->getParam('product_id');
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return ;
        }

        if (!$product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId)) {
            /* @var $product \Magento\Catalog\Model\Product */
            $session->addError(__('There are not enough parameters.'));
            $this->_redirectUrl($backUrl);
            return ;
        }

        try {
            $model = $this->_objectManager->create('Magento\ProductAlert\Model\Stock')
                ->setCustomerId($this->_objectManager->get('Magento\Customer\Model\Session')->getId())
                ->setProductId($product->getId())
                ->setWebsiteId(
                    $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getWebsiteId()
                );
            $model->save();
            $session->addSuccess(__('Alert subscription has been saved.'));
        }
        catch (\Exception $e) {
            $session->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->_redirectReferer();
    }
}
