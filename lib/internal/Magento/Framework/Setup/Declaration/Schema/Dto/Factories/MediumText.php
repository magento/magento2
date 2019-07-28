<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Medium text DTO element factory.
 */
class MediumText implements FactoryInterface
{
    /**
     * Default medium text length.
     */
    const DEFAULT_TEXT_LENGTH = 16777216;

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
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Text::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        if (!isset($data['length'])) {
            $data['length'] = self::DEFAULT_TEXT_LENGTH;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
