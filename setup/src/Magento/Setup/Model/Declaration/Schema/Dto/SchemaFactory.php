<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Magento\Setup\Model\Declaration\Schema\Dto\Schema
 */
class SchemaFactory
{
    /**
     * @var string
     */
    private $instanceName = Schema::class;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * SchemaFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Schema
     */
    public function create()
    {
        return $this->objectManager->create($this->instanceName);
    }
}
