<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;

/**
 * Class \Magento\Framework\View\Layout\Reader\Move
 *
 * @since 2.0.0
 */
class Move implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_MOVE = 'move';
    /**#@-*/

    /**
     * {@inheritdoc}
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_MOVE];
    }

    /**
     * {@inheritdoc}
     *
     * @param Context $readerContext
     * @param Layout\Element $currentElement
     * @return $this
     * @since 2.0.0
     */
    public function interpret(Context $readerContext, Layout\Element $currentElement)
    {
        $this->scheduleMove($readerContext->getScheduledStructure(), $currentElement);
        return $this;
    }

    /**
     * Schedule structural changes for move directive
     *
     * @param \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Layout\Element $currentElement
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     * @since 2.0.0
     */
    protected function scheduleMove(Layout\ScheduledStructure $scheduledStructure, Layout\Element $currentElement)
    {
        $elementName = (string)$currentElement->getAttribute('element');
        $destination = (string)$currentElement->getAttribute('destination');
        $alias = (string)$currentElement->getAttribute('as') ?: '';
        if ($elementName && $destination) {
            list($siblingName, $isAfter) = $this->beforeAfterToSibling($currentElement);
            $scheduledStructure->setElementToMove(
                $elementName,
                [$destination, $siblingName, $isAfter, $alias]
            );
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Element name and destination must be specified.')
            );
        }
        return $this;
    }

    /**
     * Analyze "before" and "after" information in the node and return sibling name and whether "after" or "before"
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @return array
     * @since 2.0.0
     */
    protected function beforeAfterToSibling($node)
    {
        $result = [null, true];
        if (isset($node['after'])) {
            $result[0] = (string)$node['after'];
        } elseif (isset($node['before'])) {
            $result[0] = (string)$node['before'];
            $result[1] = false;
        }
        return $result;
    }
}
