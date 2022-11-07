<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for @see \Magento\Framework\View\Asset\PreProcessor\Chain
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
 */
class ChainFactory implements ChainFactoryInterface
{
    /**
     * Object manager
     *
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
     * @inheritdoc
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(Chain::class, $arguments);
    }
}
