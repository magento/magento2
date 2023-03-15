<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Controller;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\SendFriend\Helper\Data as SendFriendHelper;
use Magento\SendFriend\Model\SendFriend;

/**
 * Email to a Friend Product Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Product extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FormKeyValidator $formKeyValidator
     * @param SendFriend $sendFriend
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FormKeyValidator $formKeyValidator,
        protected readonly SendFriend $sendFriend,
        protected readonly ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_formKeyValidator = $formKeyValidator;
    }

    /**
     * Check if module is enabled
     *
     * If allow only for customer - redirect to login page
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        /* @var $helper SendFriendHelper */
        $helper = $this->_objectManager->get(SendFriendHelper::class);
        /* @var CustomerSession $session */
        $session = $this->_objectManager->get(CustomerSession::class);

        if (!$helper->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        if (!$helper->isAllowForGuest() && !$session->authenticate()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'sendemail') {
                $session->setBeforeAuthUrl($this->_url->getUrl('sendfriend/product/send', ['_current' => true]));
                $this->_objectManager->get(CatalogSession::class)
                    ->setSendfriendFormData($request->getPostValue());
            }
        }
        return parent::dispatch($request);
    }

    /**
     * Initialize Product Instance
     *
     * @return ModelProduct|bool
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        if (!$productId) {
            return false;
        }
        try {
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInSiteVisibility() || !$product->isVisibleInCatalog()) {
                return false;
            }
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }

        $this->_coreRegistry->register('product', $product);
        return $product;
    }
}
