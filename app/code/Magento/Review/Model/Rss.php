<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Review\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Model Rss
 *
 * Class \Magento\Catalog\Model\Rss\Product\Rss
 */
class Rss extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Rss constructor.
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param ReviewFactory $reviewFactory
     * @param \Magento\Framework\Model\Context|null $context
     * @param \Magento\Framework\Registry|null $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Model\Context $context = null,
        \Magento\Framework\Registry $registry = null,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->eventManager = $eventManager;
        $context = $context ?? ObjectManager::getInstance()->get(\Magento\Framework\Model\Context::class);
        $registry = $registry ?? ObjectManager::getInstance()->get(\Magento\Framework\Registry::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get Product Collection
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
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
