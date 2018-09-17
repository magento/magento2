<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page\Config\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Html reader is used for collecting attributes of html in to the scheduled page structure
 */
class Html implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_HTML = 'html';
    /**#@-*/

    /**#@+
     * Supported html elements
     */
    const HTML_ATTRIBUTE = 'attribute';
    /**#@-*/

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_HTML];
    }

    /**
     * {@inheritdoc}
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Element $htmlElement
     * @return $this
     */
    public function interpret(
        Layout\Reader\Context $readerContext,
        Layout\Element $htmlElement
    ) {
        /** @var \Magento\Framework\View\Layout\Element $element */
        foreach ($htmlElement as $element) {
            if ($element->getName() === self::HTML_ATTRIBUTE) {
                $readerContext->getPageConfigStructure()->setElementAttribute(
                    PageConfig::ELEMENT_TYPE_HTML,
                    $element->getAttribute('name'),
                    $element->getAttribute('value')
                );
            }
        }
        return $this;
    }
}
