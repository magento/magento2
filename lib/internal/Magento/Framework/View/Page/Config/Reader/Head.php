<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Page\Config\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config as PageConfig;

class Head implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_HEAD = 'head';
    /**#@-*/

    /**#@+
     * Supported head elements
     */
    const HEAD_CSS = 'css';

    const HEAD_SCRIPT = 'script';

    const HEAD_LINK = 'link';

    const HEAD_REMOVE = 'remove';

    const HEAD_TITLE = 'title';

    const HEAD_META = 'meta';

    const HEAD_ATTRIBUTE = 'attribute';
    /**#@-*/

    /**
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_HEAD];
    }

    /**
     * Process Head structure
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Element $headElement
     * @param Layout\Element $parentElement
     * @return $this
     */
    public function process(
        Layout\Reader\Context $readerContext,
        Layout\Element $headElement,
        Layout\Element $parentElement
    ) {
        /** @var \Magento\Framework\View\Layout\Element $node */
        foreach ($headElement as $node) {
            switch ($node->getName()) {
                case self::HEAD_CSS:
                case self::HEAD_SCRIPT:
                case self::HEAD_LINK:
                    $readerContext->getPageConfigStructure()
                        ->addAssets($node->getAttribute('src'), $this->getAttributes($node));
                    break;

                case self::HEAD_REMOVE:
                    $readerContext->getPageConfigStructure()->removeAssets($node->getAttribute('src'));
                    break;

                case self::HEAD_TITLE:
                    $readerContext->getPageConfigStructure()->setTitle($node);
                    break;

                case self::HEAD_META:
                    $readerContext->getPageConfigStructure()
                        ->setMetaData($node->getAttribute('name'), $node->getAttribute('content'));
                    break;

                case self::HEAD_ATTRIBUTE:
                    $readerContext->getPageConfigStructure()->setElementAttribute(
                        PageConfig::ELEMENT_TYPE_HEAD,
                        $node->getAttribute('name'),
                        $node->getAttribute('value')
                    );
                    break;

                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\View\Layout\Element $element
     * @return array
     */
    protected function getAttributes($element)
    {
        $attributes = [];
        foreach ($element->attributes() as $attrName => $attrValue) {
            $attributes[$attrName] = (string)$attrValue;
        }
        return $attributes;
    }
}
