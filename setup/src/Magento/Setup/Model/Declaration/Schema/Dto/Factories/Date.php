<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Date caster
 * Remove default and nullable attributes, as date type must not have any attributes
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
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Date::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Set shape to floating point, that is by default (10,0)
     *
     * {@inheritdoc}
     * @return array
     */
    public function create(array $data)
    {
        unset($data['nullable']);
        unset($data['default']);

        return $this->objectManager->create($this->className, $data);
    }
}
