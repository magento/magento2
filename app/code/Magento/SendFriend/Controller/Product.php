<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Controller;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Email to a Friend Product Controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Product extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\SendFriend\Model\SendFriend
     */
    protected $sendFriend;

    /** @var  \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\SendFriend\Model\SendFriend $sendFriend
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\SendFriend\Model\SendFriend $sendFriend,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_formKeyValidator = $formKeyValidator;
        $this->sendFriend = $sendFriend;
        $this->productRepository = $productRepository;
    }

    /**
     * Check if module is enabled
     * If allow only for customer - redirect to login page
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        /* @var $helper \Magento\SendFriend\Helper\Data */
        $helper = $this->_objectManager->get('Magento\SendFriend\Helper\Data');
        /* @var $session \Magento\Customer\Model\Session */
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');

        if (!$helper->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        if (!$helper->isAllowForGuest() && !$session->authenticate()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'sendemail') {
                $session->setBeforeAuthUrl($this->_url->getUrl('sendfriend/product/send', ['_current' => true]));
                $this->_objectManager->get('Magento\Catalog\Model\Session')
                    ->setSendfriendFormData($request->getPostValue());
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
        $productId = (int)$this->getRequest()->getParam('id');
        if (!$productId) {
            return false;
        }
        try {
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog()) {
                return false;
            }
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }

        $this->_coreRegistry->register('product', $product);
        return $product;
    }
}
