<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class BlockFactory
 *
 * @api
 * @since 100.0.2
 */
class BlockFactory
{
    /**
     * Object manager
     *
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
     * @param string $blockName
     * @param array $arguments
     * @return \Magento\Framework\View\Element\BlockInterface
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
