<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout\ScheduledStructure\Helper;
use Magento\Framework\View\Layout\ReaderInterface;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\Config\DataInterfaceFactory;

/**
 * Class UiComponent
 */
class UiComponent implements ReaderInterface
{
    /**
     * Supported types.
     */
    const TYPE_UI_COMPONENT = 'uiComponent';

    /**
     * List of supported attributes
     *
     * @var array
     */
    protected $attributes = ['group', 'component', 'aclResource'];

    /**
     * @var Helper
     */
    protected $layoutHelper;

    /**
     * @var Condition
     * @since 2.2.0
     */
    private $conditionReader;

    /**
     * @var DataInterfaceFactory
     * @since 2.2.0
     */
    private $uiConfigFactory;

    /**
     * @var ReaderPool
     * @since 2.2.0
     */
    private $readerPool;

    /**
     * Constructor
     *
     * @param Helper $helper
     * @param Condition $conditionReader
     * @param DataInterfaceFactory $uiConfigFactory
     * @param ReaderPool $readerPool
     */
    public function __construct(
        Helper $helper,
        Condition $conditionReader,
        DataInterfaceFactory $uiConfigFactory,
        ReaderPool $readerPool
    ) {
        $this->layoutHelper = $helper;
        $this->conditionReader = $conditionReader;
        $this->uiConfigFactory = $uiConfigFactory;
        $this->readerPool = $readerPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_UI_COMPONENT];
    }

    /**
     * {@inheritdoc}
     */
    public function interpret(Context $readerContext, Element $currentElement)
    {
        $attributes = $this->getAttributes($currentElement);
        $scheduledStructure = $readerContext->getScheduledStructure();
        $referenceName = $this->layoutHelper->scheduleStructure(
            $scheduledStructure,
            $currentElement,
            $currentElement->getParent(),
            ['attributes' => $attributes]
        );
        $attributes = array_merge(
            $attributes,
            ['visibilityConditions' => $this->conditionReader->parseConditions($currentElement)]
        );
        $scheduledStructure->setStructureElementData($referenceName, ['attributes' => $attributes]);

        $elements = [];
        $config = $this->uiConfigFactory->create(['componentName' => $referenceName])->get($referenceName);
        $this->getLayoutElementsFromUiConfiguration([$referenceName => $config], $elements);
        foreach ($elements as $layoutElement) {
            $layoutElement = simplexml_load_string(
                $layoutElement,
                Element::class
            );
            $this->readerPool->interpret($readerContext, $layoutElement);
        }

        return $this;
    }

    /**
     * Find layout elements in UI configuration for correct layout generation
     *
     * @param array $config
     * @param array $elements
     * @return void
     * @since 2.2.0
     */
    private function getLayoutElementsFromUiConfiguration(array $config, array &$elements = [])
    {
        foreach ($config as $data) {
            if (isset($data['arguments']['block']['layout'])) {
                $elements[] = $data['arguments']['block']['layout'];
            }
            if (isset($data['children']) && !empty($data['children'])) {
                $this->getLayoutElementsFromUiConfiguration($data['children'], $elements);
            }
        }
    }

    /**
     * Get ui component attributes
     *
     * @param Element $element
     * @return array
     */
    protected function getAttributes(Element $element)
    {
        $attributes = [];
        foreach ($this->attributes as $attributeName) {
            $attributes[$attributeName] = (string)$element->getAttribute($attributeName);
        }

        return $attributes;
    }
}
