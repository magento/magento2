<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Real type DTO element factory.
 *
 * Used for real numbers DTO elements like decimal, float or double.
 * Decimal type is highly recommended for business math.
 */
class Real implements FactoryInterface
{
    /**
     * Default SQL precision.
     */
    const DEFAULT_PRECISION = "10";

    /**
     * Default SQL scale.
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
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Real::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * @inheritdoc
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
            $data['default'] = (float)$data['default'];
        }

        return $this->objectManager->create($this->className, $data);
    }
}
