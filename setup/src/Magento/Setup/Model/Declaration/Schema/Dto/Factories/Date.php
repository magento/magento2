<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Date factory.
 * Remove default and nullable attributes, as date type must not have any attributes.
 */
class Date implements FactoryInterface
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
     * @param string                 $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Date::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        return $this->objectManager->create($this->className, $data);
    }
}
