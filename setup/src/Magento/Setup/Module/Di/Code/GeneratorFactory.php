<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code;

use Magento\Framework\ObjectManagerInterface;

class GeneratorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
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
     */
    public function create($arguments = [])
    {
        return $this->objectManager->create('Magento\Setup\Module\Di\Code\Generator', $arguments);
    }
}
