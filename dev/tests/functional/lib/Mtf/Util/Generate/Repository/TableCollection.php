<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Mtf\Util\Generate\Repository;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

/**
 * Class CollectionProvider
 *
 */
class TableCollection extends AbstractCollection
{
    /**
     * @var array
     */
    protected $fixture;

    /**
     * @constructor
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param null $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     * @param array $fixture
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null,
        array $fixture = []
    ) {
        $this->setModel('Magento\Framework\Object');
        $this->setResourceModel('Mtf\Util\Generate\Repository\Resource');

        $resource = $this->getResource();
        $resource->setFixture($fixture);

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Get resource instance
     *
     * @return \Mtf\Util\Generate\Repository\Resource
     */
    public function getResource()
    {
        return parent::getResource();
    }
}
