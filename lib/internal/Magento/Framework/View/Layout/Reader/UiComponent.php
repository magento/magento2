<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\App;
use Magento\Framework\View\Layout;

/**
 * Class UiComponent
 */
class UiComponent implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_UI_COMPONENT = 'uiComponent';
    /**#@-*/

    /**
     * List of supported attributes
     *
     * @var array
     */
    protected $attributes = ['group', 'component', 'acl'];

    /**
     * @var Layout\ScheduledStructure\Helper
     */
    protected $layoutHelper;

    /**
     * @var string|null
     */
    protected $scopeType;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param string|null $scopeType
     */
    public function __construct(Layout\ScheduledStructure\Helper $helper, $scopeType = null)
    {
        $this->layoutHelper = $helper;
        $this->scopeType = $scopeType;
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
    public function interpret(Context $readerContext, Layout\Element $currentElement)
    {
        $attributes = $this->getAttributes($currentElement);
        $scheduledStructure = $readerContext->getScheduledStructure();
        $referenceName = $this->layoutHelper->scheduleStructure(
            $scheduledStructure,
            $currentElement,
            $currentElement->getParent(),
            ['attributes' => $attributes]
        );

        $scheduledStructure->setStructureElementData($referenceName, ['attributes' => $attributes]);
        $configPath = (string)$currentElement->getAttribute('ifconfig');
        if (!empty($configPath)) {
            $scheduledStructure->setElementToIfconfigList($referenceName, $configPath, $this->scopeType);
        }

        return $this;
    }

    /**
     * Get ui component attributes
     *
     * @param Layout\Element $element
     * @return array
     */
    protected function getAttributes(Layout\Element $element)
    {
        $attributes = [];
        foreach ($this->attributes as $attributeName) {
            $attributes[$attributeName] = (string)$element->getAttribute($attributeName);
        }

        return $attributes;
    }
}
