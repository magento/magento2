<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\BulkDescription;

use Magento\Framework\Bulk\BulkSummaryInterface;

/**
 * Class for grid options
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory
     */
    private $bulkCollectionFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * Options constructor.
     * @param \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollection
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollection,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->bulkCollectionFactory = $bulkCollection;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection $collection */
        $collection = $this->bulkCollectionFactory->create();

        /** @var \Magento\Framework\DB\Select $select */
        $select = $collection->getSelect();
        $select->reset();
        $select->distinct(true);
        $select->from($collection->getMainTable(), ['description']);
        $select->where('user_id = ?', $this->userContext->getUserId());

        $options = [];

        /** @var BulkSummaryInterface $item */
        foreach ($collection->getItems() as $item) {
            $options[] = [
                'value' => $item->getDescription(),
                'label' => $item->getDescription()
            ];
        }
        return $options;
    }
}
