<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\App\State as AppState;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Layout model
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Layout extends \Magento\Framework\Simplexml\Config implements \Magento\Framework\View\LayoutInterface
{

    /**
     * Empty layout xml
     */
    const LAYOUT_NODE = '<layout/>';

    /**
     * Layout Update module
     *
     * @var \Magento\Framework\View\Layout\ProcessorInterface
     */
    protected $_update;

    /**
     * Blocks registry
     *
     * @var array
     */
    protected $_blocks = [];

    /**
     * Cache of elements to output during rendering
     *
     * @var array
     */
    protected $_output = [];

    /**
     * Helper blocks cache for this layout
     *
     * @var array
     */
    protected $sharedBlocks = [];

    /**
     * A variable for transporting output into observer during rendering
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_renderingOutput;

    /**
     * Cache of generated elements' HTML
     *
     * @var array
     */
    protected $_renderElementCache = [];

    /**
     * Layout structure model
     *
     * @var Layout\Data\Structure
     */
    protected $structure;

    /**
     * Renderers registered for particular name
     *
     * @var array
     */
    protected $_renderers = [];

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory
     */
    protected $_processorFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var bool
     */
    protected $isPrivate = false;

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface
     */
    protected $themeResolver;

    /**
     * @var Layout\ReaderPool
     */
    protected $readerPool;

    /**
     * @var bool
     */
    protected $cacheable;

    /**
     * @var \Magento\Framework\View\Layout\GeneratorPool
     */
    protected $generatorPool;

    /**
     * @var \Magento\Framework\View\Layout\BuilderInterface
     */
    protected $builder;

    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var Layout\Reader\ContextFactory
     */
    protected $readerContextFactory;

    /**
     * @var Layout\Generator\ContextFactory
     */
    protected $generatorContextFactory;

    /**
     * @var Layout\Reader\Context
     */
    protected $readerContext;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Layout\ProcessorFactory $processorFactory
     * @param ManagerInterface $eventManager
     * @param Layout\Data\Structure $structure
     * @param MessageManagerInterface $messageManager
     * @param Design\Theme\ResolverInterface $themeResolver
     * @param Layout\ReaderPool $readerPool
     * @param Layout\GeneratorPool $generatorPool
     * @param FrontendInterface $cache
     * @param Layout\Reader\ContextFactory $readerContextFactory
     * @param Layout\Generator\ContextFactory $generatorContextFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Psr\Log\LoggerInterface $logger
     * @param bool $cacheable
     */
    public function __construct(
        Layout\ProcessorFactory $processorFactory,
        ManagerInterface $eventManager,
        Layout\Data\Structure $structure,
        MessageManagerInterface $messageManager,
        Design\Theme\ResolverInterface $themeResolver,
        Layout\ReaderPool $readerPool,
        Layout\GeneratorPool $generatorPool,
        FrontendInterface $cache,
        Layout\Reader\ContextFactory $readerContextFactory,
        Layout\Generator\ContextFactory $generatorContextFactory,
        AppState $appState,
        Logger $logger,
        $cacheable = true
    ) {
        $this->_elementClass = 'Magento\Framework\View\Layout\Element';
        $this->_renderingOutput = new \Magento\Framework\DataObject();

        $this->_processorFactory = $processorFactory;
        $this->_eventManager = $eventManager;
        $this->structure = $structure;
        $this->messageManager = $messageManager;
        $this->themeResolver = $themeResolver;
        $this->readerPool = $readerPool;
        $this->generatorPool = $generatorPool;
        $this->cacheable = $cacheable;
        $this->cache = $cache;
        $this->readerContextFactory = $readerContextFactory;
        $this->generatorContextFactory = $generatorContextFactory;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * @param Layout\GeneratorPool $generatorPool
     * @return $this
     */
    public function setGeneratorPool(Layout\GeneratorPool $generatorPool)
    {
        $this->generatorPool = $generatorPool;
        return $this;
    }

    /**
     * @param Layout\BuilderInterface $builder
     * @return $this
     */
    public function setBuilder(Layout\BuilderInterface $builder)
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * Build layout blocks from generic layouts and/or page configurations
     *
     * @return void
     */
    protected function build()
    {
        if (!empty($this->builder)) {
            $this->builder->build();
        }
    }

    /**
     * TODO Will be eliminated in MAGETWO-28359
     * @return void
     */
    public function publicBuild()
    {
        $this->build();
    }

    /**
     * Cleanup circular references between layout & blocks
     *
     * Destructor should be called explicitly in order to work around the PHP bug
     * https://bugs.php.net/bug.php?id=62468
     */
    public function __destruct()
    {
        if (isset($this->_update) && is_object($this->_update)) {
            $this->_update->__destruct();
            $this->_update = null;
        }
        $this->_blocks = [];
        parent::__destruct();
    }

    /**
     * Retrieve the layout update instance
     *
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    public function getUpdate()
    {
        if (!$this->_update) {
            $theme = $this->themeResolver->get();
            $this->_update = $this->_processorFactory->create(['theme' => $theme]);
        }
        return $this->_update;
    }

    /**
     * Layout xml generation
     *
     * @return $this
     */
    public function generateXml()
    {
        $xml = $this->getUpdate()->asSimplexml();
        $this->setXml($xml);
        $this->structure->importElements([]);
        return $this;
    }

    /**
     * Create structure of elements from the loaded XML configuration
     *
     * @return void
     */
    public function generateElements()
    {
        \Magento\Framework\Profiler::start(__CLASS__ . '::' . __METHOD__);
        $cacheId = 'structure_' . $this->getUpdate()->getCacheId();
        $result = $this->cache->load($cacheId);
        if ($result) {
            $this->readerContext = unserialize($result);
        } else {
            \Magento\Framework\Profiler::start('build_structure');
            $this->readerPool->interpret($this->getReaderContext(), $this->getNode());
            \Magento\Framework\Profiler::stop('build_structure');
            $this->cache->save(serialize($this->getReaderContext()), $cacheId, $this->getUpdate()->getHandles());
        }

        $generatorContext = $this->generatorContextFactory->create(
            [
                'structure' => $this->structure,
                'layout' => $this,
            ]
        );

        \Magento\Framework\Profiler::start('generate_elements');
        $this->generatorPool->process($this->getReaderContext(), $generatorContext);
        \Magento\Framework\Profiler::stop('generate_elements');

        $this->addToOutputRootContainers();
        \Magento\Framework\Profiler::stop(__CLASS__ . '::' . __METHOD__);
    }

    /**
     * Add parent containers to output
     *
     * @return $this
     */
    protected function addToOutputRootContainers()
    {
        foreach ($this->structure->exportElements() as $name => $element) {
            if ($element['type'] === Element::TYPE_CONTAINER && empty($element['parent'])) {
                $this->addOutputElement($name);
            }
        }
        return $this;
    }

    /**
     * Get child block if exists
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    public function getChildBlock($parentName, $alias)
    {
        $this->build();
        $name = $this->structure->getChildId($parentName, $alias);
        if ($this->isBlock($name)) {
            return $this->getBlock($name);
        }
        return false;
    }

    /**
     * Set child element into layout structure
     *
     * @param string $parentName
     * @param string $elementName
     * @param string $alias
     * @return $this
     */
    public function setChild($parentName, $elementName, $alias)
    {
        $this->build();
        $this->structure->setAsChild($elementName, $parentName, $alias);
        return $this;
    }

    /**
     * Reorder a child of a specified element
     *
     * If $offsetOrSibling is null, it will put the element to the end
     * If $offsetOrSibling is numeric (integer) value, it will put the element after/before specified position
     * Otherwise -- after/before specified sibling
     *
     * @param string $parentName
     * @param string $childName
     * @param string|int|null $offsetOrSibling
     * @param bool $after
     * @return void
     */
    public function reorderChild($parentName, $childName, $offsetOrSibling, $after = true)
    {
        $this->build();
        $this->structure->reorderChildElement($parentName, $childName, $offsetOrSibling, $after);
    }

    /**
     * Remove child element from parent
     *
     * @param string $parentName
     * @param string $alias
     * @return $this
     */
    public function unsetChild($parentName, $alias)
    {
        $this->build();
        $this->structure->unsetChild($parentName, $alias);
        return $this;
    }

    /**
     * Get list of child names
     *
     * @param string $parentName
     * @return array
     */
    public function getChildNames($parentName)
    {
        $this->build();
        return array_keys($this->structure->getChildren($parentName));
    }

    /**
     * Get list of child blocks
     *
     * Returns associative array of <alias> => <block instance>
     *
     * @param string $parentName
     * @return array
     */
    public function getChildBlocks($parentName)
    {
        $this->build();
        $blocks = [];
        foreach ($this->structure->getChildren($parentName) as $childName => $alias) {
            $block = $this->getBlock($childName);
            if ($block) {
                $blocks[$alias] = $block;
            }
        }
        return $blocks;
    }

    /**
     * Get child name by alias
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|string
     */
    public function getChildName($parentName, $alias)
    {
        $this->build();
        return $this->structure->getChildId($parentName, $alias);
    }

    /**
     * Find an element in layout, render it and return string with its output
     *
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function renderElement($name, $useCache = true)
    {
        $this->build();
        if (!isset($this->_renderElementCache[$name]) || !$useCache) {
            if ($this->displayElement($name)) {
                $this->_renderElementCache[$name] = $this->renderNonCachedElement($name);
            } else {
                return $this->_renderElementCache[$name] = '';
            }
        }
        $this->_renderingOutput->setData('output', $this->_renderElementCache[$name]);
        $this->_eventManager->dispatch(
            'core_layout_render_element',
            ['element_name' => $name, 'layout' => $this, 'transport' => $this->_renderingOutput]
        );
        return $this->_renderingOutput->getData('output');
    }

    /**
     * Define whether to display element
     * Display if 'display' attribute is absent (false, null) or equal true ('1', true, 'true')
     * In any other cases - do not display
     *
     * @param string $name
     * @return bool
     */
    protected function displayElement($name)
    {
        $display = $this->structure->getAttribute($name, 'display');
        if ($display === '' || $display === false || $display === null
            || filter_var($display, FILTER_VALIDATE_BOOLEAN)) {

            return true;
        }
        return false;
    }

    /**
     * Render non cached element
     *
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function renderNonCachedElement($name)
    {
        $result = '';
        try {
            if ($this->isUiComponent($name)) {
                $result = $this->_renderUiComponent($name);
            } elseif ($this->isBlock($name)) {
                $result = $this->_renderBlock($name);
            } else {
                $result = $this->_renderContainer($name, false);
            }
        } catch (\Exception $e) {
            if ($this->appState->getMode() === AppState::MODE_DEVELOPER) {
                throw $e;
            }
            $message = ($e instanceof LocalizedException) ? $e->getLogMessage() : $e->getMessage();
            $this->logger->critical($message);
        }
        return $result;
    }

    /**
     * Gets HTML of block element
     *
     * @param string $name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _renderBlock($name)
    {
        $block = $this->getBlock($name);
        return $block ? $block->toHtml() : '';
    }

    /**
     * Gets HTML of Ui Component
     *
     * @param string $name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _renderUiComponent($name)
    {
        $uiComponent = $this->getUiComponent($name);
        return $uiComponent ? $uiComponent->toHtml() : '';
    }

    /**
     * Gets HTML of container element
     *
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    protected function _renderContainer($name, $useCache = true)
    {
        $html = '';
        $children = $this->getChildNames($name);
        foreach ($children as $child) {
            $html .= $this->renderElement($child, $useCache);
        }
        if ($html == '' || !$this->structure->getAttribute($name, Element::CONTAINER_OPT_HTML_TAG)) {
            return $html;
        }

        $htmlId = $this->structure->getAttribute($name, Element::CONTAINER_OPT_HTML_ID);
        if ($htmlId) {
            $htmlId = ' id="' . $htmlId . '"';
        }

        $htmlClass = $this->structure->getAttribute($name, Element::CONTAINER_OPT_HTML_CLASS);
        if ($htmlClass) {
            $htmlClass = ' class="' . $htmlClass . '"';
        }

        $htmlTag = $this->structure->getAttribute($name, Element::CONTAINER_OPT_HTML_TAG);

        $html = sprintf('<%1$s%2$s%3$s>%4$s</%1$s>', $htmlTag, $htmlId, $htmlClass, $html);

        return $html;
    }

    /**
     * Add element to parent group
     *
     * @param string $blockName
     * @param string $parentGroupName
     * @return bool
     */
    public function addToParentGroup($blockName, $parentGroupName)
    {
        $this->build();
        return $this->structure->addToParentGroup($blockName, $parentGroupName);
    }

    /**
     * Get element names for specified group
     *
     * @param string $blockName
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($blockName, $groupName)
    {
        $this->build();
        return $this->structure->getGroupChildNames($blockName, $groupName);
    }

    /**
     * Check if element exists in layout structure
     *
     * @param string $name
     * @return bool
     */
    public function hasElement($name)
    {
        $this->build();
        return $this->structure->hasElement($name);
    }

    /**
     * Get property value of an element
     *
     * @param string $name
     * @param string $attribute
     * @return mixed
     */
    public function getElementProperty($name, $attribute)
    {
        $this->build();
        return $this->structure->getAttribute($name, $attribute);
    }

    /**
     * Whether specified element is a block
     *
     * @param string $name
     * @return bool
     */
    public function isBlock($name)
    {
        $this->build();
        if ($this->structure->hasElement($name)) {
            return Element::TYPE_BLOCK === $this->structure->getAttribute($name, 'type');
        }
        return false;
    }

    /**
     * Whether specified element is a UI Component
     *
     * @param string $name
     * @return bool
     */
    public function isUiComponent($name)
    {
        $this->build();
        if ($this->structure->hasElement($name)) {
            return Element::TYPE_UI_COMPONENT === $this->structure->getAttribute($name, 'type');
        }
        return false;
    }

    /**
     * Checks if element with specified name is container
     *
     * @param string $name
     * @return bool
     */
    public function isContainer($name)
    {
        $this->build();
        if ($this->structure->hasElement($name)) {
            return Element::TYPE_CONTAINER === $this->structure->getAttribute($name, 'type');
        }
        return false;
    }

    /**
     * Whether the specified element may be manipulated externally
     *
     * @param string $name
     * @return bool
     */
    public function isManipulationAllowed($name)
    {
        $this->build();
        $parentName = $this->structure->getParentId($name);
        return $parentName && $this->isContainer($parentName);
    }

    /**
     * Save block in blocks registry
     *
     * @param string $name
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function setBlock($name, $block)
    {
        $this->_blocks[$name] = $block;
        return $this;
    }

    /**
     * Remove block from registry
     *
     * @param string $name
     * @return $this
     */
    public function unsetElement($name)
    {
        $this->build();
        if (isset($this->_blocks[$name])) {
            $this->_blocks[$name] = null;
            unset($this->_blocks[$name]);
        }
        $this->structure->unsetElement($name);
        return $this;
    }

    /**
     * Block Factory
     *
     * @param string $type
     * @param string $name
     * @param array $arguments
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function createBlock($type, $name = '', array $arguments = [])
    {
        $this->build();
        $name = $this->structure->createStructuralElement($name, Element::TYPE_BLOCK, $type);
        $block = $this->_createBlock($type, $name, $arguments);
        $block->setLayout($this);
        return $block;
    }

    /**
     * Create block and add to layout
     *
     * @param string $type
     * @param string $name
     * @param array $arguments
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _createBlock($type, $name, array $arguments = [])
    {
        /** @var \Magento\Framework\View\Layout\Generator\Block $blockGenerator */
        $blockGenerator = $this->generatorPool->getGenerator(Layout\Generator\Block::TYPE);
        $block = $blockGenerator->createBlock($type, $name, $arguments);
        $this->setBlock($name, $block);
        return $block;
    }

    /**
     * Add a block to registry, create new object if needed
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @param string $name
     * @param string $parent
     * @param string $alias
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '')
    {
        $this->build();
        if ($block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $name = $name ?: $block->getNameInLayout();
        } else {
            $block = $this->_createBlock($block, $name);
        }
        $name = $this->structure->createStructuralElement(
            $name,
            Element::TYPE_BLOCK,
            $name ?: get_class($block)
        );
        $this->setBlock($name, $block);
        $block->setNameInLayout($name);
        if ($parent) {
            $this->structure->setAsChild($name, $parent, $alias);
        }
        $block->setLayout($this);
        return $block;
    }

    /**
     * Insert container into layout structure
     *
     * @param string $name
     * @param string $label
     * @param array $options
     * @param string $parent
     * @param string $alias
     * @return void
     */
    public function addContainer($name, $label, array $options = [], $parent = '', $alias = '')
    {
        $this->build();
        $name = $this->structure->createStructuralElement($name, Element::TYPE_CONTAINER, $alias);
        $options[Layout\Element::CONTAINER_OPT_LABEL] = $label;
        /** @var \Magento\Framework\View\Layout\Generator\Container $containerGenerator */
        $containerGenerator = $this->generatorPool->getGenerator(Layout\Generator\Container::TYPE);
        $containerGenerator->generateContainer($this->structure, $name, $options);
        if ($parent) {
            $this->structure->setAsChild($name, $parent, $alias);
        }
    }

    /**
     * Rename element in layout and layout structure
     *
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function renameElement($oldName, $newName)
    {
        $this->build();
        if (isset($this->_blocks[$oldName])) {
            $block = $this->_blocks[$oldName];
            $this->_blocks[$oldName] = null;
            unset($this->_blocks[$oldName]);
            $this->_blocks[$newName] = $block;
        }
        $this->structure->renameElement($oldName, $newName);

        return $this;
    }

    /**
     * Retrieve all blocks from registry as array
     *
     * @return array
     */
    public function getAllBlocks()
    {
        $this->build();
        return $this->_blocks;
    }

    /**
     * Get block object by name
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function getBlock($name)
    {
        $this->build();
        if (isset($this->_blocks[$name])) {
            return $this->_blocks[$name];
        } else {
            return false;
        }
    }

    /**
     * Get Ui Component object by name
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function getUiComponent($name)
    {
        return $this->getBlock($name);
    }

    /**
     * Gets parent name of an element with specified name
     *
     * @param string $childName
     * @return bool|string
     */
    public function getParentName($childName)
    {
        $this->build();
        return $this->structure->getParentId($childName);
    }

    /**
     * Get element alias by name
     *
     * @param string $name
     * @return bool|string
     */
    public function getElementAlias($name)
    {
        $this->build();
        return $this->structure->getChildAlias($this->structure->getParentId($name), $name);
    }

    /**
     * Add an element to output
     *
     * @param string $name
     * @return $this
     */
    public function addOutputElement($name)
    {
        $this->_output[$name] = $name;
        return $this;
    }

    /**
     * Remove an element from output
     *
     * @param string $name
     * @return $this
     */
    public function removeOutputElement($name)
    {
        if (isset($this->_output[$name])) {
            unset($this->_output[$name]);
        }
        return $this;
    }

    /**
     * Get all blocks marked for output
     *
     * @return string
     */
    public function getOutput()
    {
        $this->build();
        $out = '';
        foreach ($this->_output as $name) {
            $out .= $this->renderElement($name);
        }
        return $out;
    }

    /**
     * Retrieve messages block
     *
     * @return \Magento\Framework\View\Element\Messages
     */
    public function getMessagesBlock()
    {
        $this->build();
        $block = $this->getBlock('messages');
        if ($block) {
            return $block;
        }
        return $this->createBlock('Magento\Framework\View\Element\Messages', 'messages');
    }

    /**
     * Get block singleton
     *
     * @param string $type
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockSingleton($type)
    {
        if (empty($type)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Invalid block type')
            );
        }
        if (!isset($this->sharedBlocks[$type])) {
            $this->sharedBlocks[$type] = $this->createBlock($type);
        }
        return $this->sharedBlocks[$type];
    }

    /**
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @param string $type
     * @param string $template
     * @param array $data
     * @return $this
     */
    public function addAdjustableRenderer($namespace, $staticType, $dynamicType, $type, $template, $data = [])
    {
        $this->_renderers[$namespace][$staticType][$dynamicType] = [
            'type' => $type,
            'template' => $template,
            'data' => $data,
        ];
        return $this;
    }

    /**
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @return array|null
     */
    public function getRendererOptions($namespace, $staticType, $dynamicType)
    {
        if (!isset($this->_renderers[$namespace])) {
            return null;
        }
        if (!isset($this->_renderers[$namespace][$staticType])) {
            return null;
        }
        if (!isset($this->_renderers[$namespace][$staticType][$dynamicType])) {
            return null;
        }
        return $this->_renderers[$namespace][$staticType][$dynamicType];
    }

    /**
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @param array $data
     * @return void
     */
    public function executeRenderer($namespace, $staticType, $dynamicType, $data = [])
    {
        $this->build();
        if ($options = $this->getRendererOptions($namespace, $staticType, $dynamicType)) {
            $dictionary = [];
            /** @var $block \Magento\Framework\View\Element\Template */
            $block = $this->createBlock($options['type'], '')
                ->setData($data)
                ->assign($dictionary)
                ->setTemplate($options['template'])
                ->assign($data);

            echo $this->_renderBlock($block->getNameInLayout());
        }
    }

    /**
     * Init messages by message storage(s), loading and adding messages to layout messages block
     *
     * @param string|array $messageGroups
     * @return void
     * @throws \UnexpectedValueException
     */
    public function initMessages($messageGroups = [])
    {
        $this->build();
        foreach ($this->_prepareMessageGroup($messageGroups) as $group) {
            $block = $this->getMessagesBlock();
            $block->addMessages($this->messageManager->getMessages(true, $group));
            $block->addStorageType($group);
        }
    }

    /**
     * Validate message groups
     *
     * @param array $messageGroups
     * @return array
     */
    protected function _prepareMessageGroup($messageGroups)
    {
        if (!is_array($messageGroups)) {
            $messageGroups = [$messageGroups];
        } elseif (empty($messageGroups)) {
            $messageGroups[] = $this->messageManager->getDefaultGroup();
        }
        return $messageGroups;
    }

    /**
     * Check is exists non-cacheable layout elements
     *
     * @return bool
     */
    public function isCacheable()
    {
        $this->build();
        $cacheableXml = !(bool)count($this->getXml()->xpath('//' . Element::TYPE_BLOCK . '[@cacheable="false"]'));
        return $this->cacheable && $cacheableXml;
    }

    /**
     * Check is exists non-cacheable layout elements
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * Mark layout as private
     *
     * @param bool $isPrivate
     * @return Layout
     */
    public function setIsPrivate($isPrivate = true)
    {
        $this->isPrivate = (bool)$isPrivate;
        return $this;
    }

    /**
     * Getter and lazy loader for xml element
     *
     * @return \Magento\Framework\Simplexml\Element
     */
    protected function getXml()
    {
        if (!$this->_xml) {
            $this->setXml(simplexml_load_string(self::LAYOUT_NODE, $this->_elementClass));
        }
        return $this->_xml;
    }

    /**
     * Getter and lazy loader for reader context
     *
     * @return Layout\Reader\Context
     */
    public function getReaderContext()
    {
        if (!$this->readerContext) {
            $this->readerContext = $this->readerContextFactory->create();
        }
        return $this->readerContext;
    }
}
