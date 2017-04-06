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
    protected $attributes = ['group', 'component', 'aclResource', 'visibilityCondition'];

    /**
     * @var Helper
     */
    protected $layoutHelper;

    /**
     * @var Condition
     */
    private $conditionReader;

    /**
     * Constructor
     *
     * @param Helper $helper
     * @param Condition $conditionReader
     */
    public function __construct(
        Helper $helper,
        Condition $conditionReader
    ) {
        $this->layoutHelper = $helper;
        $this->conditionReader = $conditionReader;
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

        return $this;
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
