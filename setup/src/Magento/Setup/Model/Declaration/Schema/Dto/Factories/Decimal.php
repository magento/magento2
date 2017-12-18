<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * This type is equal to SQL DECIMAL(SCALE,PRECISION) type. Usually it is used for accurate operations
 * with decimal numbers. For example, for price
 * Usually decimal is concatinated from 2 integers, so it has not round problems
 */
class Decimal implements FactoryInterface
{
    /**
     * Default SQL precission
     */
    const DEFAULT_PRECISSION = "10";

    /**
     * Default SQL scale
     */
    const DEFAULT_SCALE = "0";

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
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Decimal::class
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
        if (!isset($data['precission'])) {
            $data['precission'] = self::DEFAULT_PRECISSION;
        }

        if (!isset($data['scale'])) {
            $data['scale'] = self::DEFAULT_SCALE;
        }

        if (isset($data['default'])) {
            $data['default'] = (float) $data['default'];
        }

        return $this->objectManager->create($this->className, $data);
    }
}
