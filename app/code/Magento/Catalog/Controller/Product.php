<?php
/**
 * Product controller.
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
namespace Magento\Catalog\Controller;

use Magento\Catalog\Model\Product as ModelProduct;

class Product extends \Magento\Framework\App\Action\Action implements \Magento\Catalog\Controller\Product\View\ViewInterface
{
    /**
     * Initialize requested product object
     *
     * @return ModelProduct
     */
    protected function _initProduct()
    {
        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');

        $params = new \Magento\Framework\Object();
        $params->setCategoryId($categoryId);

        return $this->_objectManager->get('Magento\Catalog\Helper\Product')->initProduct($productId, $this, $params);
    }

    /**
     * Initialize product view layout
     *
     * @param ModelProduct $product
     * @return $this
     */
    protected function _initProductLayout($product)
    {
        $this->_objectManager->get('Magento\Catalog\Helper\Product\View')->initProductLayout($product, $this);
        return $this;
    }

    /**
     * Product view action
     *
     * @return void
     */

    /**
     * Product view action
     *
     * @return void
     */
    public function viewAction()
    {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $product = $this->_initProduct();
            if (!$product) {
                $this->noProductRedirect();
            }
            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNotice($notice);
            }
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
            return;
        }

        // Prepare helper and params
        /** @var \Magento\Catalog\Helper\Product\View $viewHelper */
        $viewHelper = $this->_objectManager->get('Magento\Catalog\Helper\Product\View');

        $params = new \Magento\Framework\Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (\Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                $this->noProductRedirect();
            } else {
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->_forward('noroute');
            }
        }
    }

    /**
     * View product gallery action
     *
     * @return void
     */
    public function galleryAction()
    {
        if (!$this->_initProduct()) {
            if (isset($_GET['store']) && !$this->getResponse()->isRedirect()) {
                $this->_redirect('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noroute');
            }
            return;
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Redirect if product failed to load
     *
     * @return void
     */
    protected function noProductRedirect()
    {
        if (isset($_GET['store']) && !$this->getResponse()->isRedirect()) {
            $this->_redirect('');
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noroute');
        }
    }
}
