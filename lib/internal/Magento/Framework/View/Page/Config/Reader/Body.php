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
                $this->setBodyAttributeTosStructure($readerContext, $element);
            }
        }
        return $this->readerPool->interpret($readerContext, $bodyElement);
    }

    /**
     * Schedule attributes to the page config structure
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Element $element
     * @return $this
     */
    protected function setBodyAttributeTosStructure(Layout\Reader\Context $readerContext, Layout\Element $element)
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
