<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Layout;

class Block implements Layout\GeneratorInterface
{
    /**
     * Type of generator
     */
    const TYPE = 'block';

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->blockFactory = $blockFactory;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Creates block object based on data and add it to the layout
     *
     * @param Layout\Reader\Context $readerContext
     * @param Context $generatorContext
     * @return $this
     */
    public function process(Layout\Reader\Context $readerContext, Layout\Generator\Context $generatorContext)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        $layout = $generatorContext->getLayout();
        $structure = $generatorContext->getStructure();
        /** @var $blocks \Magento\Framework\View\Element\AbstractBlock[] */
        $blocks = [];
        $blockActions = [];
        // Instantiate blocks and collect all actions data
        foreach ($scheduledStructure->getElements() as $elementName => $element) {
            list($type, $data) = $element;
            if ($type === self::TYPE) {
                $block = $this->generateBlock($scheduledStructure, $structure, $elementName);
                $blocks[$elementName] = $block;
                $layout->setBlock($elementName, $block);
                if (!empty($data['actions'])) {
                    $blockActions[$elementName] = $data['actions'];
                }
            }
        }
        // Set layout instance to all generated block (trigger _prepareLayout method)
        foreach ($blocks as $elementName => $block) {
            $block->setLayout($layout);
            $this->eventManager->dispatch('core_layout_block_create_after', ['block' => $block]);
            $scheduledStructure->unsetElement($elementName);
        }
        // Run all actions after layout initialization
        foreach ($blockActions as $elementName => $actions) {
            foreach ($actions as $action) {
                list($methodName, $actionArguments) = $action;
                $this->generateAction($blocks[$elementName], $methodName, $actionArguments);
            }
        }
        return $this;
    }

    /**
     * Create block and set related data
     *
     * @param \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Layout\Data\Structure $structure
     * @param string $elementName
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function generateBlock(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Data\Structure $structure,
        $elementName
    ) {
        list(, $data) = $scheduledStructure->getElement($elementName);
        $attributes = $data['attributes'];

        if (!empty($attributes['group'])) {
            $structure->addToParentGroup($elementName, $attributes['group']);
        }

        // create block
        $className = $attributes['class'];
        $block = $this->createBlock($className, $elementName, [
            'data' => $this->evaluateArguments($data['arguments'])
        ]);
        if (!empty($attributes['template'])) {
            $block->setTemplate($attributes['template']);
        }
        if (!empty($attributes['ttl'])) {
            $ttl = (int)$attributes['ttl'];
            $block->setTtl($ttl);
        }
        return $block;
    }

    /**
     * Create block instance
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @param string $name
     * @param array $arguments
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function createBlock($block, $name, array $arguments = [])
    {
        $block = $this->getBlockInstance($block, $arguments);
        $block->setType(get_class($block));
        $block->setNameInLayout($name);
        $block->addData(isset($arguments['data']) ? $arguments['data'] : []);
        return $block;
    }

    /**
     * Create block object instance based on block type
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @param array $arguments
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function getBlockInstance($block, array $arguments = [])
    {
        if ($block && is_string($block)) {
            try {
                $block = $this->blockFactory->createBlock($block, $arguments);
            } catch (\ReflectionException $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        if (!$block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            throw new \Magento\Framework\Model\Exception(__('Invalid block type: %1', $block));
        }
        return $block;
    }

    /**
     * Run action defined in layout update
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @param string $methodName
     * @param array $actionArguments
     * @return void
     */
    protected function generateAction($block, $methodName, $actionArguments)
    {
        $profilerKey = 'BLOCK_ACTION:' . $block->getNameInLayout() . '>' . $methodName;
        \Magento\Framework\Profiler::start($profilerKey);
        $args = $this->evaluateArguments($actionArguments);
        call_user_func_array([$block, $methodName], $args);
        \Magento\Framework\Profiler::stop($profilerKey);
    }

    /**
     * Compute and return argument values
     *
     * @param array $arguments
     * @return array
     */
    protected function evaluateArguments(array $arguments)
    {
        $result = [];
        foreach ($arguments as $argumentName => $argumentData) {
            $result[$argumentName] = $this->argumentInterpreter->evaluate($argumentData);
        }
        return $result;
    }
}
