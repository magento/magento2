<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;

/**
 * Class Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\View\Element\ExceptionHandlerBlock
     */
    protected $exceptionHandlerBlockFactory;

    /**
     * Default block class name. Will be used if no class name is specified in block configuration
     *
     * @var string
     */
    private $defaultClass;

    /**
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\View\Element\ExceptionHandlerBlockFactory $exceptionHandlerBlockFactory
     * @param State $appState
     * @param string $defaultClass
     */
    public function __construct(
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\View\Element\ExceptionHandlerBlockFactory $exceptionHandlerBlockFactory,
        State $appState,
        $defaultClass = Template::class
    ) {
        $this->blockFactory = $blockFactory;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->scopeResolver = $scopeResolver;
        $this->exceptionHandlerBlockFactory = $exceptionHandlerBlockFactory;
        $this->appState = $appState;
        $this->defaultClass = $defaultClass;
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                try {
                    $block = $this->generateBlock($scheduledStructure, $structure, $elementName);
                    $blocks[$elementName] = $block;
                    $layout->setBlock($elementName, $block);
                    if (!empty($data['actions'])) {
                        $blockActions[$elementName] = $data['actions'];
                    }
                } catch (\Exception $e) {
                    $this->handleRenderException($e);
                    unset($blocks[$elementName]);
                }
            }
        }
        // Set layout instance to all generated block (trigger _prepareLayout method)
        foreach ($blocks as $elementName => $block) {
            try {
                $block->setLayout($layout);
                $this->eventManager->dispatch('core_layout_block_create_after', ['block' => $block]);
            } catch (\Exception $e) {
                $this->handleRenderException($e);
                $layout->setBlock(
                    $elementName,
                    $this->exceptionHandlerBlockFactory->create(['blockName' => $elementName])
                );
                unset($blockActions[$elementName]);
            }
            $scheduledStructure->unsetElement($elementName);
        }
        // Run all actions after layout initialization
        foreach ($blockActions as $elementName => $actions) {
            try {
                foreach ($actions as $action) {
                    list($methodName, $actionArguments, $configPath, $scopeType) = $action;
                    if (empty($configPath)
                        || $this->scopeConfig->isSetFlag($configPath, $scopeType, $this->scopeResolver->getScope())
                    ) {
                        $this->generateAction($blocks[$elementName], $methodName, $actionArguments);
                    }
                }
            } catch (\Exception $e) {
                $this->handleRenderException($e);
                $layout->setBlock(
                    $elementName,
                    $this->exceptionHandlerBlockFactory->create(['blockName' => $elementName])
                );
            }
        }
        return $this;
    }

    /**
     * Handle exceptions during rendering process
     *
     * @param \Exception $cause
     * @throws \Exception
     * @return void
     */
    protected function handleRenderException(\Exception $cause)
    {
        if ($this->appState->getMode() === State::MODE_DEVELOPER) {
            throw $cause;
        }
        $message = ($cause instanceof LocalizedException) ? $cause->getLogMessage() : $cause->getMessage();
        $this->logger->critical($message);
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
        if (!empty($attributes['display'])) {
            $structure->setAttribute($elementName, 'display', $attributes['display']);
        }

        // create block
        $className = isset($attributes['class']) && !empty($attributes['class']) ?
            $attributes['class'] : $this->defaultClass;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function getBlockInstance($block, array $arguments = [])
    {
        $e = null;
        if ($block && is_string($block)) {
            try {
                $block = $this->blockFactory->createBlock($block, $arguments);
            } catch (\ReflectionException $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        if (!$block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    'Invalid block type: %1',
                    [is_object($block) ? get_class($block) : (string) $block]
                ),
                $e
            );
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
            if (!isset($argumentData[Dom::TYPE_ATTRIBUTE])) {
                $result[$argumentName] = $argumentData;
                continue;
            }
            $result[$argumentName] = $this->argumentInterpreter->evaluate($argumentData);
        }
        return $result;
    }
}
