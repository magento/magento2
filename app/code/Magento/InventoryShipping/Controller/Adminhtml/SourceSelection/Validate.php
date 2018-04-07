<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Controller\Adminhtml\SourceSelection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Validate extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);

        $sourceCode = $this->getRequest()->getParam('sourceCode');
        $items = $this->getRequest()->getParam('items');

        //TODO: This is simple check. Need to create separate service and add additional checks:
        //TODO: 1. manage stock
        //TODO: 2. sum of all qty less on equal to source available qty (for products that occur twice or more in order)

        try {
            $itemsToShip = [];
            foreach ($items as $item) {
                if (empty($item['sources'])) {
                    continue;
                }
                foreach ($item['sources'] as $source) {
                    if ($source['sourceCode'] == $sourceCode) {
                        if ((float)$source['qtyToDeduct'] > (float)$source['qtyAvailable']) {
                            throw new LocalizedException(
                                __('Qty of %1 should be less or equal to %2', $item['sku'], $source['qtyAvailable'])
                            );
                        }
                        $itemsToShip[$item['sku']] = ($itemsToShip[$item['sku']] ?? 0) + $source['qtyToDeduct'];
                    }
                }
            }
        } catch (LocalizedException $e) {
            $response->setError(true);
            $response->setMessages([$e->getMessage()]);
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}
