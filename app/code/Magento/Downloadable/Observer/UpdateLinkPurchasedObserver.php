<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Assign Downloadable links to customer created after issuing guest order.
 */
class UpdateLinkPurchasedObserver implements ObserverInterface
{
    /**
     * Core store config
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory
     */
    private $purchasedFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    private $objectCopyService;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory $purchasedFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory $purchasedFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->purchasedFactory = $purchasedFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Re-save order data after order update.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        $purchasedLinks = $this->purchasedFactory->create()->addFieldToFilter(
            'order_id',
            ['eq' => $order->getId()]
        );

        foreach ($purchasedLinks as $linkPurchased) {
            $this->objectCopyService->copyFieldsetToTarget(
                \downloadable_sales_copy_order::class,
                'to_downloadable',
                $order,
                $linkPurchased
            );
            $linkPurchased->save();
        }

        return $this;
    }
}
