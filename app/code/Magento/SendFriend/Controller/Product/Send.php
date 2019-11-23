<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Controller\Product;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Controller class. Represents rendering and request flow
 */
class Send extends \Magento\SendFriend\Controller\Product implements HttpGetActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\SendFriend\Model\SendFriend $sendFriend
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Session $catalogSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\SendFriend\Model\SendFriend $sendFriend,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $product = $this->_initProduct();

        if (!$product) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        if ($this->sendFriend->getMaxSendsToFriend() && $this->sendFriend->isExceedLimit()) {
            $this->messageManager->addNotice(
                __('You can\'t send messages more than %1 times an hour.', $this->sendFriend->getMaxSendsToFriend())
            );
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_eventManager->dispatch('sendfriend_product', ['product' => $product]);
        $data = $this->catalogSession->getSendfriendFormData();
        if ($data) {
            $this->catalogSession->setSendfriendFormData(true);
            $block = $resultPage->getLayout()->getBlock('sendfriend.send');
            if ($block) {
                /** @var \Magento\SendFriend\Block\Send $block */
                $block->setFormData($data);
            }
        }

        return $resultPage;
    }
}
