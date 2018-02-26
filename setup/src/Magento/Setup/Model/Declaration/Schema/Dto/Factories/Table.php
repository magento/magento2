<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;

/**
 * Table DTO element factory.
 */
class Table implements FactoryInterface
{
    /**
     * Default engine.
     * May be overriden for another DBMS.
     */
    const DEFAULT_ENGINE = 'innodb';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ResourceConnection $resourceConnection
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Table::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        if ($data['engine'] === null) {
            $data['engine'] = self::DEFAULT_ENGINE;
        }
        $tablePrefix = $this->resourceConnection->getTablePrefix();
        $nameWithoutPrefix = $data['name'];
        if (!empty($tablePrefix) && strpos($nameWithoutPrefix, $tablePrefix) === 0) {
            $data['nameWithoutPrefix'] = str_replace($tablePrefix, "", $data['name']);
        } else {
            $data['name'] = $tablePrefix . $data['name'];
            $data['nameWithoutPrefix'] = $nameWithoutPrefix;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
