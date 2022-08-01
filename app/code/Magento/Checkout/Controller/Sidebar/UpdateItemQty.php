<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Sidebar;

use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class used to update item quantity.
 */
class UpdateItemQty extends Action implements HttpPostActionInterface
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
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Sidebar $sidebar,
        LoggerInterface $logger,
        Data $jsonHelper
    ) {
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Action for Quantity update
     *
     * @return $this
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $itemQty = (int)$this->getRequest()->getParam('item_qty');

        if ($itemQty <= 0) {
            return  $this->jsonResponse(__('Invalid Item Quantity Requested.'));
        }

        try {
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
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($error))
        );
    }
}
