<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\App\ObjectManager;

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
     * Object manager config
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ?ConfigInterface $config = null
    ) {
        $this->objectManager = $objectManager;
        $this->config        = $config ?: ObjectManager::getInstance()->get(ConfigInterface::class);
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
        $blockName  = ltrim($blockName, '\\');
        $arguments  = $this->getConfigArguments($blockName, $arguments);
        $block      = $this->objectManager->create($blockName, $arguments);
        if (!$block instanceof BlockInterface) {
            throw new \LogicException($blockName . ' does not implement BlockInterface');
        }
        if ($block instanceof Template) {
            $block->setTemplateContext($block);
        }
        return $block;
    }

    /**
     * Get All Config Arguments based on Block Name
     *
     * @param string $blockName
     * @param array $arguments
     * @return array $arguments
     */
    private function getConfigArguments($blockName, array $arguments = [])
    {
        if (!$this->config) {
            return $arguments;
        }
        $configArguments    = $this->config->getArguments($blockName);
        if ($configArguments && isset($configArguments['data'])) {
            $arguments['data'] = ($arguments && isset($arguments['data'])) ?
                array_merge($arguments['data'], $configArguments['data']) : $configArguments['data'];
        }
        return $arguments;
    }
}
