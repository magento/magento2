<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Sidebar;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;

class UpdateItemQty implements HttpPostActionInterface
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param RequestQuantityProcessor|null $quantityProcessor
     * @codeCoverageIgnore
     */
    public function __construct(
        Sidebar $sidebar,
        LoggerInterface $logger,
        Data $jsonHelper,
        RequestInterface  $request,
        ResponseInterface $response,
        ?RequestQuantityProcessor $quantityProcessor = null
    ) {
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->request = $request;
        $this->response = $response;
        $this->quantityProcessor = $quantityProcessor
            ?? ObjectManager::getInstance()->get(RequestQuantityProcessor::class);
    }

    /**
     * Action for Quantity update
     *
     * @return $this
     */
    public function execute()
    {
        $itemId = (int)$this->request->getParam('item_id');
        $itemQty = $this->request->getParam('item_qty');

        if (!is_numeric($itemQty) || ($itemQty <=0)) {
            $e = new InputException(__('A non-numeric value found')) ;
            return  $this->jsonResponse($e->getMessage());
        }
        try {
            $itemQty = $this->quantityProcessor->prepareQuantity($itemQty*1);
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->updateQuoteItem($itemId, $itemQty);
            return $this->jsonResponse();
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return Http
     */
    protected function jsonResponse($error = '')
    {
        return $this->response->representJson(
            $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($error))
        );
    }
}
