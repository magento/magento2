<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Layout;

class UiComponent implements Layout\GeneratorInterface
{
    /**
     * Generator type
     */
    const TYPE = 'ui_component';

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * Constructor
     *
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter
     */
    public function __construct(
        UiComponentFactory $uiComponentFactory,
        InterpreterInterface $argumentInterpreter
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->argumentInterpreter = $argumentInterpreter;
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
     * Creates UI Component object based on scheduled data and add it to the layout
     *
     * @param Layout\Reader\Context $readerContext
     * @param Context $generatorContext
     * @return $this
     */
    public function process(Layout\Reader\Context $readerContext, Layout\Generator\Context $generatorContext)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        $scheduledElements = $scheduledStructure->getElements();
        if (!$scheduledElements) {
            return $this;
        }
        $structure = $generatorContext->getStructure();
        $layout = $generatorContext->getLayout();
        $this->uiComponentFactory->setLayout($layout);
        /** @var $blocks \Magento\Framework\View\Element\AbstractBlock[] */
        $blocks = [];
        // Instantiate blocks and collect all actions data
        foreach ($scheduledElements as $elementName => $element) {
            list($type, $data) = $element;
            if ($type === self::TYPE) {
                $block = $this->generateComponent($structure, $elementName, $data);
                $blocks[$elementName] = $block;
                $layout->setBlock($elementName, $block);
                $scheduledStructure->unsetElement($elementName);
            }
        }

        return $this;
    }

    /**
     * Create component object
     *
     * @param \Magento\Framework\View\Layout\Data\Structure $structure
     * @param string $elementName
     * @param string $data
     * @return \Magento\Framework\View\Element\UiComponentInterface
     */
    protected function generateComponent(Layout\Data\Structure $structure, $elementName, $data)
    {
        $attributes = $data['attributes'];
        if (!empty($attributes['group'])) {
            $structure->addToParentGroup($elementName, $attributes['group']);
        }
        $arguments = empty($data['arguments']) ? [] : $this->evaluateArguments($data['arguments']);
        $componentName = isset($attributes['component']) ? $attributes['component'] : '';
        $uiComponent = $this->uiComponentFactory->createUiComponent($componentName, $elementName, $arguments);
        return $uiComponent;
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
