<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * This type is equal to SQL TYPE(SCALE,PRECISION) type.
 * Used for real numbers storage like decimal, float or double.
 * Decimal type is highly recommended for business math.
 */
class Real implements FactoryInterface
{
    /**
     * Default SQL precision
     */
    const DEFAULT_PRECISION = "0";

    /**
     * Default SQL scale
     */
    const DEFAULT_SCALE = "10";

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
     * @param string                 $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Set shape to fixed point, that is by default (10,0) for decimal and (0,0) for double or float
     *
     * {@inheritdoc}
     *
     * @return array
     */
    public function create(array $data)
    {
        if (!isset($data['precision'])) {
            $data['precision'] = ($data['type'] === 'decimal') ? self::DEFAULT_PRECISION : 0;
        }

        if (!isset($data['scale'])) {
            $data['scale'] = ($data['type'] === 'decimal') ? self::DEFAULT_SCALE : 0;
        }

        if (isset($data['default'])) {
            $data['default'] = floatval($data['default']);
        }

        return $this->objectManager->create($this->className, $data);
    }
}
