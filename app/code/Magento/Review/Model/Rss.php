<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model;

/**
 * Class Rss
 * @package Magento\Catalog\Model\Rss\Product
 * @since 2.0.0
 */
class Rss extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Review\Model\ReviewFactory
     * @since 2.0.0
     */
    protected $reviewFactory;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param ReviewFactory $reviewFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @since 2.0.0
     */
    public function getProductCollection()
    {
        /** @var $reviewModel \Magento\Review\Model\Review */
        $reviewModel = $this->reviewFactory->create();
        $collection = $reviewModel->getProductCollection()
            ->addStatusFilter($reviewModel->getPendingStatus())
            ->addAttributeToSelect('name', 'inner')
            ->setDateOrder();

        $this->eventManager->dispatch('rss_catalog_review_collection_select', ['collection' => $collection]);
        return $collection;
    }
}
