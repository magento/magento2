<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\DataSavior;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory which allows to create SQL select generator
 */
class SelectGeneratorFactory
{
    /**
     * @var string
     */
    private $instanceName;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * SelectGeneratorFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = SelectGenerator::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return SelectGenerator
     */
    public function create()
    {
        return $this->objectManager->create($this->instanceName);
    }
}
