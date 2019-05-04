<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * StringBinary DTO element factory.
 *
 * Used for char, varchar, binary, varbinary types.
 */
class StringBinary implements FactoryInterface
{
    /**
     * Default data length.
     */
    const DEFAULT_TEXT_LENGTH = 255;

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
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\StringBinary::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $data['length'] = isset($data['length']) ? (int) $data['length'] : self::DEFAULT_TEXT_LENGTH;
        return $this->objectManager->create($this->className, $data);
    }
}
