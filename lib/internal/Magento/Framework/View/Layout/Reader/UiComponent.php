<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\App;
use Magento\Framework\View\Layout;

class UiComponent implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_UI_COMPONENT = 'ui_component';
    /**#@-*/

    /**
     * List of supported attributes
     *
     * @var array
     */
    protected $attributes = ['group', 'component'];

    /**
     * @var Layout\ScheduledStructure\Helper
     */
    protected $layoutHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var string|null
     */
    protected $scopeType;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param string|null $scopeType
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        App\Config\ScopeConfigInterface $scopeConfig,
        App\ScopeResolverInterface $scopeResolver,
        $scopeType = null
    ) {
        $this->layoutHelper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->scopeResolver = $scopeResolver;
        $this->scopeType = $scopeType;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_UI_COMPONENT];
    }

    /**
     * {@inheritdoc}
     *
     * @param Context $readerContext
     * @param Layout\Element $currentElement
     * @return $this
     */
    public function interpret(Context $readerContext, Layout\Element $currentElement)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        $referenceName = $this->layoutHelper->scheduleStructure(
            $readerContext->getScheduledStructure(),
            $currentElement,
            $currentElement->getParent(),
            ['attributes' => $this->getAttributes($currentElement)]
        );
        $scheduledStructure->setStructureElementData($referenceName, [
            'attributes' => $this->getAttributes($currentElement)
        ]);
        $configPath = (string)$currentElement->getAttribute('ifconfig');
        if (!empty($configPath)
            && !$this->scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())
        ) {
            $scheduledStructure->setElementToRemoveList($referenceName);
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
