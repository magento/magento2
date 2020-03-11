<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Api\ReorderInterface;
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
     * @var \Magento\Sales\Api\ReorderInterface
     */
    private $reorder;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param Registry $registry
     * @param ReorderHelper|null $reorderHelper
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry,
        ReorderHelper $reorderHelper = null,
        \Magento\Sales\Api\ReorderInterface $reOrder = null
    ) {
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
        $this->reorder = $reOrder ?: ObjectManager::getInstance()->get(ReorderInterface::class);
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

        $errors = $reorderOutput->getLineItemErrors();
        if (!empty($errors)) {
            $useNotice = $this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true);
            foreach ($errors as $error) {
                $useNotice
                    ? $this->messageManager->addNoticeMessage($error->getMessage())
                    : $this->messageManager->addErrorMessage($error->getMessage());
            }
        }

        return $resultRedirect->setPath('checkout/cart');
    }
}
