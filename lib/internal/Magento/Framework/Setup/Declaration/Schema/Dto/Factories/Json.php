<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Json
 *
 * Json Factory
 */
class Json implements FactoryInterface
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
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Blob::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Create element using definition data array.
     *
     * @param array $data
     * @return \Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface|mixed
     */
    public function create(array $data)
    {
        return $this->objectManager->create($this->className, $data);
    }
}
