<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\ObjectManager\ConfigInterface;
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
     * @var ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface|null $objectManagerConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ?ConfigInterface $objectManagerConfig = null
    ) {
        $this->objectManager = $objectManager;
        $this->objectManagerConfig = $objectManagerConfig ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(ConfigInterface::class);
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
        $resolvedType = $this->objectManagerConfig->getInstanceType(
            $this->objectManagerConfig->getPreference($blockName)
        );
        if (!is_a($resolvedType, BlockInterface::class, true)) {
            throw new \LogicException($blockName . ' does not implement BlockInterface');
        }
        $block = $this->objectManager->create($blockName, $arguments);
        if ($block instanceof Template) {
            $block->setTemplateContext($block);
        }
        return $block;
    }
}
