<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver
 *
 * @since 2.0.0
 */
class CatalogBlockProductCollectionBeforeToHtmlObserver implements ObserverInterface
{
    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     * @since 2.0.0
     */
    protected $_reviewFactory;

    /**
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        $this->_reviewFactory = $reviewFactory;
    }

    /**
     * Append review summary before rendering html
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection instanceof \Magento\Framework\Data\Collection) {
            $productCollection->load();
            $this->_reviewFactory->create()->appendSummary($productCollection);
        }

        return $this;
    }
}
