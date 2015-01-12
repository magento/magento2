<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;

class Remove implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_REMOVE = 'remove';
    /**#@-*/

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_REMOVE];
    }

    /**
     * {@inheritdoc}
     *
     * @param Context $readerContext
     * @param Layout\Element $currentElement
     * @param Layout\Element $parentElement
     * @return $this
     */
    public function interpret(Context $readerContext, Layout\Element $currentElement)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        $scheduledStructure->setElementToRemoveList((string)$currentElement->getAttribute('name'));
        return $this;
    }
}
