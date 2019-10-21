<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal;

/**
 * Primary key DTO element factory.
 *
 * By default primary key can have only one name - PRIMARY to prevent duplicate primary key creation attempts.
 */
class Primary implements FactoryInterface
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
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $data['name'] = Internal::PRIMARY_NAME;
        $data['nameWithoutPrefix'] = $data['name'];
        return $this->objectManager->create($this->className, $data);
    }
}
