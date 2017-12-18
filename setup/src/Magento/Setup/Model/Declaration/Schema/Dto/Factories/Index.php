<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Serves needs of indexes, that speedup DB
 */
class Index implements FactoryInterface
{
    /**
     * Default padding number
     */
    const DEFAULT_INDEX_TYPE = "BTREE";

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Index::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Set default on delete to foreign key
     *
     * {@inheritdoc}
     * @return array
     */
    public function create(array $data)
    {
        if (!isset($data['type'])) {
            $data['type'] = self::DEFAULT_INDEX_TYPE;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
