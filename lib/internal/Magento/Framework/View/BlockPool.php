<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class BlockPool
 * @since 2.0.0
 */
class BlockPool
{
    /**
     * Block factory
     * @var \Magento\Framework\View\Element\BlockFactory
     * @since 2.0.0
     */
    protected $blockFactory;

    /**
     * Blocks
     *
     * @var array
     * @since 2.0.0
     */
    protected $blocks = [];

    /**
     * Constructor
     *
     * @param BlockFactory $blockFactory
     * @since 2.0.0
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Add a block
     *
     * @param string $name
     * @param string $class
     * @param array $arguments [optional]
     * @return BlockPool
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function add($name, $class, array $arguments = [])
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(
                (string)new \Magento\Framework\Phrase('Invalid Block class name: %1', [$class])
            );
        }

        $block = $this->blockFactory->createBlock($class, $arguments);

        $this->blocks[$name] = $block;

        return $this;
    }

    /**
     * Get blocks
     *
     * @param string $name
     * @return BlockInterface|null
     * @since 2.0.0
     */
    public function get($name = null)
    {
        if (!isset($name)) {
            return $this->blocks;
        }

        return isset($this->blocks[$name]) ? $this->blocks[$name] : null;
    }
}
