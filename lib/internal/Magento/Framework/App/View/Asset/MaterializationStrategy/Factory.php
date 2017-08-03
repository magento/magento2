<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset\MaterializationStrategy;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset;

/**
 * Class \Magento\Framework\App\View\Asset\MaterializationStrategy\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Strategies list
     *
     * @var array
     * @since 2.0.0
     */
    protected $strategiesList;

    /**
     * Default strategy key
     */
    const DEFAULT_STRATEGY = \Magento\Framework\App\View\Asset\MaterializationStrategy\Copy::class;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param StrategyInterface[] $strategiesList
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager, $strategiesList = [])
    {
        $this->objectManager = $objectManager;
        $this->strategiesList = $strategiesList;
    }

    /**
     * Create materialization strategy basing on asset
     *
     * @param Asset\LocalInterface $asset
     * @return StrategyInterface
     *
     * @throws \LogicException
     * @since 2.0.0
     */
    public function create(Asset\LocalInterface $asset)
    {
        if (empty($this->strategiesList)) {
            $this->strategiesList[] = $this->objectManager->get(self::DEFAULT_STRATEGY);
        }

        foreach ($this->strategiesList as $strategy) {
            if ($strategy->isSupported($asset)) {
                return $strategy;
            }
        }

        throw new \LogicException('No materialization strategy is supported');
    }
}
