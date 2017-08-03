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
 * @since 2.0.0
 */
class ChainFactory implements ChainFactoryInterface
{
    /**
     * Object manager
     *
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
     * @inheritdoc
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(Chain::class, $arguments);
    }
}
