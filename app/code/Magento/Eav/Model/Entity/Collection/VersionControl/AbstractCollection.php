<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Collection\VersionControl;

/**
 * Class Abstract Collection
 * @api
 * @since 2.0.0
 */
abstract class AbstractCollection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot
     * @since 2.0.0
     */
    protected $entitySnapshot;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
     * @param mixed $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->entitySnapshot = $entitySnapshot;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function fetchItem()
    {
        $item = parent::fetchItem();
        if ($item) {
            $this->entitySnapshot->registerSnapshot($item);
        }
        return $item;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        $this->entitySnapshot->registerSnapshot($item);
        return $item;
    }
}
