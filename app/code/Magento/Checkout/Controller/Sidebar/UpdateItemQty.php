<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Sidebar;

use Exception;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class UpdateItemQty implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Sidebar
     */
    private $sidebar;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param ResultJsonFactory $resultJsonFactory
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        ResultJsonFactory $resultJsonFactory,
        Sidebar $sidebar,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $itemId = (int)$this->request->getParam('item_id');
        $itemQty = $this->request->getParam('item_qty') * 1;
        $error = '';

        try {
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->updateQuoteItem($itemId, $itemQty);
        } catch (LocalizedException $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $this->logger->critical($e);
            $error = $e->getMessage();
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($this->sidebar->getResponseData($error));

        return $resultJson;
    }
}
