<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Controller\AbstractController;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Reorder as ReorderHelper;

/**
 * Abstract class for controllers Reorder(Customer) and Reorder(Guest)
 */
abstract class Reorder extends Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Sales\Model\Reorder\Reorder
     */
    private $reorder;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param Registry $registry
     * @param ReorderHelper|null $reorderHelper
     * @param \Magento\Sales\Model\Reorder\Reorder|null $reorder
     * @param CheckoutSession|null $checkoutSession
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry,
        ?ReorderHelper $reorderHelper = null,
        ?\Magento\Sales\Model\Reorder\Reorder $reorder = null,
        ?CheckoutSession $checkoutSession = null
    ) {
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
        $this->reorder = $reorder ?: ObjectManager::getInstance()->get(\Magento\Sales\Model\Reorder\Reorder::class);
        $this->checkoutSession = $checkoutSession ?: ObjectManager::getInstance()->get(CheckoutSession::class);
    }

    /**
     * Action for reorder
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $order = $this->_coreRegistry->registry('current_order');

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $reorderOutput = $this->reorder->execute($order->getIncrementId(), $order->getStoreId());
        } catch (LocalizedException $localizedException) {
            $this->messageManager->addErrorMessage($localizedException->getMessage());
            return $resultRedirect->setPath('checkout/cart');
        }

        // Set quote id for guest session: \Magento\Quote\Api\CartRepositoryInterface::save doesn't set quote id
        // to session for guest customer, as it does \Magento\Checkout\Model\Cart::save which is deprecated.
        $this->checkoutSession->setQuoteId($reorderOutput->getCart()->getId());

        return $resultRedirect->setPath('checkout/cart');
    }
}
