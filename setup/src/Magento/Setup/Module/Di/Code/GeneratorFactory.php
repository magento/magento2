<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Setup\Module\Di\Code\GeneratorFactory
 *
 * @since 2.0.0
 */
class GeneratorFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates operation
     *
     * @param array $arguments
     * @return Generator
     * @since 2.0.0
     */
    public function create($arguments = [])
    {
        return $this->objectManager->create(\Magento\Setup\Module\Di\Code\Generator::class, $arguments);
    }
}
