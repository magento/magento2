<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\SendFriend\Block\Send as BlockSend;
use Magento\SendFriend\Controller\Product as ControllerProduct;
use Magento\SendFriend\Model\SendFriend;

/**
 * Controller class. Represents rendering and request flow
 */
class Send extends ControllerProduct implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FormKeyValidator $formKeyValidator
     * @param SendFriend $sendFriend
     * @param ProductRepositoryInterface $productRepository
     * @param CatalogSession $catalogSession
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FormKeyValidator $formKeyValidator,
        SendFriend $sendFriend,
        ProductRepositoryInterface $productRepository,
        protected readonly CatalogSession $catalogSession
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $formKeyValidator,
            $sendFriend,
            $productRepository
        );
    }

    /**
     * Show Send to a Friend Form
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $product = $this->_initProduct();

        if (!$product) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        if ($this->sendFriend->getMaxSendsToFriend() && $this->sendFriend->isExceedLimit()) {
            $this->messageManager->addNotice(
                __('You can\'t send messages more than %1 times an hour.', $this->sendFriend->getMaxSendsToFriend())
            );
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_eventManager->dispatch('sendfriend_product', ['product' => $product]);
        $data = $this->catalogSession->getSendfriendFormData();
        if ($data) {
            $this->catalogSession->setSendfriendFormData(true);
            $block = $resultPage->getLayout()->getBlock('sendfriend.send');
            if ($block) {
                /** @var BlockSend $block */
                $block->setFormData($data);
            }
        }

        return $resultPage;
    }
}
