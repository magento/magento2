<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Review\Observer\TagProductCollectionLoadAfterObserver
 *
 */
class TagProductCollectionLoadAfterObserver implements ObserverInterface
{
    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     */
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        $this->_reviewFactory = $reviewFactory;
    }

    /**
     * Add review summary info for tagged product collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $this->_reviewFactory->create()->appendSummary($collection);

        return $this;
    }
}
