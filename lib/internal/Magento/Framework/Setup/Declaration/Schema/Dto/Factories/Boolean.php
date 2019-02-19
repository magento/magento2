<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Boolean factory.
 */
class Boolean implements FactoryInterface
{
    /**
     * Default value for boolean xsi:type.
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
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param BooleanUtils           $booleanUtils
     * @param string                 $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        BooleanUtils $booleanUtils,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        if (isset($data['default'])) {
            $data['default'] = $this->booleanUtils->toBoolean($data['default']);
        }

        return $this->objectManager->create($this->className, $data);
    }
}
