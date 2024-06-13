<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\ObjectManagerInterface;

/**
 * Creates Blocks
 *
 * @api
 * @since 100.0.2
 */
class BlockFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create block
     *
     * @template T of BlockInterface
     *
     * @param class-string<T> $blockName
     * @param array $arguments
     *
     * @return T
     *
     * @throws \LogicException
     */
    public function createBlock($blockName, array $arguments = [])
    {
        $blockName = ltrim($blockName, '\\');
        $block = $this->objectManager->create($blockName, $arguments);
        if (!$block instanceof BlockInterface) {
            throw new \LogicException($blockName . ' does not implement BlockInterface');
        }
        if ($block instanceof Template) {
            $block->setTemplateContext($block);
        }
        return $block;
    }
}
