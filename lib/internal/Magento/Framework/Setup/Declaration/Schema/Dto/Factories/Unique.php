<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;

/**
 * Unique constraint DTO element factory.
 */
class Unique implements FactoryInterface
{
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
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal::class
    ) {
        $this->objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $nameWithoutPrefix = $data['name'];

        if ($this->resourceConnection->getTablePrefix()) {
            $nameWithoutPrefix = $this->resourceConnection
                ->getConnection($data['table']->getResource())
                ->getIndexName(
                    $data['table']->getNameWithoutPrefix(),
                    $data['column'],
                    $data['type']
                );
        }

        $data['nameWithoutPrefix'] = $nameWithoutPrefix;

        return $this->objectManager->create($this->className, $data);
    }
}
