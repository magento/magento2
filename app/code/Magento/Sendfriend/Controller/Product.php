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
 * @package     Magento_Sendfriend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sendfriend\Controller;

use Magento\App\Action\NotFoundException;
use Magento\App\RequestInterface;

/**
 * Email to a Friend Product Controller
 *
 * @category    Magento
 * @package     Magento_Sedfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Registry $coreRegistry,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Check if module is enabled
     * If allow only for customer - redirect to login page
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     * @throws \Magento\App\Action\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        /* @var $helper \Magento\Sendfriend\Helper\Data */
        $helper = $this->_objectManager->get('Magento\Sendfriend\Helper\Data');
        /* @var $session \Magento\Customer\Model\Session */
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');

        if (!$helper->isEnabled()) {
            throw new NotFoundException();
        }

        if (!$helper->isAllowForGuest() && !$session->authenticate($this)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'sendemail') {
                $session->setBeforeAuthUrl($this->_objectManager
                    ->create('Magento\UrlInterface')
                    ->getUrl('*/*/send', array(
                        '_current' => true
                    )));
                $this->_objectManager->get('Magento\Catalog\Model\Session')
                    ->setSendfriendFormData($request->getPost());
            }
        }
        return parent::dispatch($request);
    }

    /**
     * Initialize Product Instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _initProduct()
    {
        $productId  = (int)$this->getRequest()->getParam('id');
        if (!$productId) {
            return false;
        }
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
            ->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            return false;
        }

        $this->_coreRegistry->register('product', $product);
        return $product;
    }

    /**
     * Initialize send friend model
     *
     * @return \Magento\Sendfriend\Model\Sendfriend
     */
    protected function _initSendToFriendModel()
    {
        /** @var \Magento\HTTP\PhpEnvironment\RemoteAddress $remoteAddress */
        $remoteAddress = $this->_objectManager->get('Magento\HTTP\PhpEnvironment\RemoteAddress');

        /** @var \Magento\Stdlib\Cookie $cookie */
        $cookie = $this->_objectManager->get('Magento\Stdlib\Cookie');

        /** @var \Magento\Core\Model\StoreManagerInterface $store */
        $store = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface');

        /** @var \Magento\Sendfriend\Model\Sendfriend $model */
        $model  = $this->_objectManager->create('Magento\Sendfriend\Model\Sendfriend');
        $model->setRemoteAddr($remoteAddress->getRemoteAddress(true));
        $model->setCookie($cookie);
        $model->setWebsiteId($store->getStore()->getWebsiteId());

        $this->_coreRegistry->register('send_to_friend_model', $model);

        return $model;
    }

    /**
     * Show Send to a Friend Form
     *
     * @return void
     */
    public function sendAction()
    {
        $product    = $this->_initProduct();
        $model      = $this->_initSendToFriendModel();

        if (!$product) {
            $this->_forward('noroute');
            return;
        }
        /* @var $session \Magento\Catalog\Model\Session */
        $catalogSession = $this->_objectManager->get('Magento\Catalog\Model\Session');

        if ($model->getMaxSendsToFriend() && $model->isExceedLimit()) {
            $this->messageManager->addNotice(
                __('You can\'t send messages more than %1 times an hour.', $model->getMaxSendsToFriend())
            );
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        $this->_eventManager->dispatch('sendfriend_product', array('product' => $product));
        $data = $catalogSession->getSendfriendFormData();
        if ($data) {
            $catalogSession->setSendfriendFormData(true);
            $block = $this->_view->getLayout()->getBlock('sendfriend.send');
            if ($block) {
                $block->setFormData($data);
            }
        }

        $this->_view->renderLayout();
    }

    /**
     * Send Email Post Action
     *
     * @return void
     */
    public function sendmailAction()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('*/*/send', array('_current' => true));
        }

        $product    = $this->_initProduct();
        $model      = $this->_initSendToFriendModel();
        $data       = $this->getRequest()->getPost();

        if (!$product || !$data) {
            $this->_forward('noroute');
            return;
        }

        $categoryId = $this->getRequest()->getParam('cat_id', null);
        if ($categoryId) {
            $category = $this->_objectManager->create('Magento\Catalog\Model\Category')
                ->load($categoryId);
            $product->setCategory($category);
            $this->_coreRegistry->register('current_category', $category);
        }

        $model->setSender($this->getRequest()->getPost('sender'));
        $model->setRecipients($this->getRequest()->getPost('recipients'));
        $model->setProduct($product);

        /* @var $session \Magento\Catalog\Model\Session */
        $catalogSession = $this->_objectManager->get('Magento\Catalog\Model\Session');
        try {
            $validate = $model->validate();
            if ($validate === true) {
                $model->send();
                $this->messageManager->addSuccess(__('The link to a friend was sent.'));
                $url = $product->getProductUrl();
                $this->getResponse()->setRedirect($this->_redirect->success($url));
                return;
            }
            else {
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We found some problems with the data.'));
                }
            }
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Some emails were not sent.'));
        }

        // save form data
        $catalogSession->setSendfriendFormData($data);

        $url = $this->_objectManager
            ->create('Magento\UrlInterface')
            ->getUrl('*/*/send', array('_current' => true));
        $this->getResponse()->setRedirect($this->_redirect->error($url));
    }
}
