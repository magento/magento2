<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Controller\Adminhtml\SourceSelection;

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
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';

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
        //TODO: 3. check total qty
        try {
            $itemsToShip = [];
            $totalQty = 0;
            foreach ($items as $item) {
                if (empty($item['sources'])) {
                    continue;
                }
                foreach ($item['sources'] as $source) {
                    if ($source['sourceCode'] == $sourceCode) {
                        if ($item['isManageStock']) {
                            $qtyToCompare = (float)$source['qtyAvailable'];
                        } else {
                            $qtyToCompare = (float)$item['qtyToShip'];
                        }
                        if ((float)$source['qtyToDeduct'] > $qtyToCompare) {
                            throw new LocalizedException(
                                __('Qty of %1 should be less or equal to %2', $item['sku'], $source['qtyAvailable'])
                            );
                        }
                        $itemsToShip[$item['sku']] = ($itemsToShip[$item['sku']] ?? 0) + $source['qtyToDeduct'];
                        $totalQty += $itemsToShip[$item['sku']];
                    }
                }
            }
            if ($totalQty == 0) {
                throw new LocalizedException(
                    __('You should select one or more items to ship.')
                );
            }
        } catch (LocalizedException $e) {
            $response->setError(true);
            $response->setMessages([$e->getMessage()]);
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}
