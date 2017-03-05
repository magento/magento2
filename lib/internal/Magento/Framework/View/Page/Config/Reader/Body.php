<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page\Config\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Body structure reader
 */
class Body implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_BODY = 'body';
    /**#@-*/

    /**#@+
     * Supported body sub elements
     */
    const BODY_ATTRIBUTE = 'attribute';
    /**#@-*/

    /**
     * @var Layout\ReaderPool
     */
    protected $readerPool;

    /**
     * Constructor
     *
     * @param Layout\ReaderPool $readerPool
     */
    public function __construct(Layout\ReaderPool $readerPool)
    {
        $this->readerPool = $readerPool;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_BODY];
    }

    /**
     * {@inheritdoc}
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Element $bodyElement
     * @return $this
     */
    public function interpret(
        Layout\Reader\Context $readerContext,
        Layout\Element $bodyElement
    ) {
        /** @var \Magento\Framework\View\Layout\Element $element */
        foreach ($bodyElement as $element) {
            if ($element->getName() === self::BODY_ATTRIBUTE) {
                $this->setBodyAttributeToStructure($readerContext, $element);
            }
        }
        $this->readerPool->interpret($readerContext, $bodyElement);
        return $this;
    }

    /**
     * Schedule attributes to the page config structure
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Element $element
     * @return $this
     */
    protected function setBodyAttributeToStructure(Layout\Reader\Context $readerContext, Layout\Element $element)
    {
        if ($element->getAttribute('name') == PageConfig::BODY_ATTRIBUTE_CLASS) {
            $readerContext->getPageConfigStructure()->setBodyClass($element->getAttribute('value'));
        } else {
            $readerContext->getPageConfigStructure()->setElementAttribute(
                PageConfig::ELEMENT_TYPE_BODY,
                $element->getAttribute('name'),
                $element->getAttribute('value')
            );
        }
        return $this;
    }
}
