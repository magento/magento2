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
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;

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
     */
    public function interpret(Context $readerContext, Layout\Element $currentElement)
    {
        $this->scheduleMove($readerContext->getScheduledStructure(), $currentElement);
        return false;
    }

    /**
     * Schedule structural changes for move directive
     *
     * @param \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Layout\Element $currentElement
     * @throws \Magento\Framework\Exception
     * @return $this
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
                array($destination, $siblingName, $isAfter, $alias)
            );
        } else {
            throw new \Magento\Framework\Exception('Element name and destination must be specified.');
        }
        return $this;
    }

    /**
     * Analyze "before" and "after" information in the node and return sibling name and whether "after" or "before"
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @return array
     */
    protected function beforeAfterToSibling($node)
    {
        $result = array(null, true);
        if (isset($node['after'])) {
            $result[0] = (string)$node['after'];
        } elseif (isset($node['before'])) {
            $result[0] = (string)$node['before'];
            $result[1] = false;
        }
        return $result;
    }
}
