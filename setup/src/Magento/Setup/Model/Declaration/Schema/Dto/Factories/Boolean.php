<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Can accept only 2 values: true or false
 */
class Boolean implements FactoryInterface
{
    /**
     * Default value for boolean xsi:type
     */
    const DEFAULT_BOOLEAN = false;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param BooleanUtils $booleanUtils
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        BooleanUtils $booleanUtils,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Convert default attribute from string to boolean value
     *
     * {@inheritdoc}
     * @return array
     */
    public function create(array $data)
    {
        if (isset($data['default'])) {
            $data['default'] = $this->booleanUtils->toBoolean($data['default']);
        } else {
            $data['default'] = self::DEFAULT_BOOLEAN;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
