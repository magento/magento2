<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset\MaterializationStrategy;

use Magento\Framework\View\Asset;

class Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Strategies list
     *
     * @var array
     */
    protected $strategiesList;

    /**
     * Default strategy key
     */
    const DEFAULT_STRATEGY = 'Magento\Framework\App\View\Asset\MaterializationStrategy\Copy';

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param StrategyInterface[] $strategiesList
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $strategiesList = [])
    {
        $this->_objectManager = $objectManager;
        $this->strategiesList = $strategiesList;
    }

    /**
     * Create materialization strategy basing on asset
     *
     * @param Asset\LocalInterface $asset
     * @return StrategyInterface
     *
     * @throws \LogicException
     */
    public function create(Asset\LocalInterface $asset)
    {
        if (empty($this->strategiesList)) {
            $this->strategiesList[] = $this->_objectManager->get(self::DEFAULT_STRATEGY);
        }

        foreach ($this->strategiesList as $strategy) {
            if ($strategy->isSupported($asset)) {
                return $strategy;
            }
        }

        throw new \LogicException(__('No materialization strategy is supported'));
    }
}
