<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sendfriend\Controller;

use Magento\Framework\App\Action\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Email to a Friend Product Controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Sendfriend\Model\Sendfriend
     */
    protected $sendFriend;

    /** @var  \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Sendfriend\Model\Sendfriend $sendFriend
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Sendfriend\Model\Sendfriend $sendFriend,
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
     * @throws \Magento\Framework\App\Action\NotFoundException
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
                $session->setBeforeAuthUrl(
                    $this->_objectManager->create(
                        'Magento\Framework\UrlInterface'
                    )->getUrl(
                        '*/*/send',
                        ['_current' => true]
                    )
                );
                $this->_objectManager->get(
                    'Magento\Catalog\Model\Session'
                )->setSendfriendFormData(
                    $request->getPost()
                );
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

    /**
     * Initialize send friend model
     *
     * @return \Magento\Sendfriend\Model\Sendfriend
     */
    protected function _initSendToFriendModel()
    {
        $this->sendFriend->register();
        return $this->sendFriend;
    }
}
