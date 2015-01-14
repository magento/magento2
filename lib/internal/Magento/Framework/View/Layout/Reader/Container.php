<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;

/**
 * Class Container
 */
class Container implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_CONTAINER = 'container';
    const TYPE_REFERENCE_CONTAINER = 'referenceContainer';
    /**#@-*/

    /**#@+
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID = 'htmlId';
    const CONTAINER_OPT_LABEL = 'label';
    /**#@-*/

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool
     */
    protected $readerPool;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\ReaderPool $readerPool
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\ReaderPool $readerPool
    ) {
        $this->helper = $helper;
        $this->readerPool = $readerPool;
    }

    /**
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_CONTAINER, self::TYPE_REFERENCE_CONTAINER];
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
        switch ($currentElement->getName()) {
            case self::TYPE_CONTAINER:
                $this->helper->scheduleStructure(
                    $readerContext->getScheduledStructure(),
                    $currentElement,
                    $currentElement->getParent()
                );
                $this->mergeContainerAttributes($readerContext->getScheduledStructure(), $currentElement);
                break;

            case self::TYPE_REFERENCE_CONTAINER:
                $this->mergeContainerAttributes($readerContext->getScheduledStructure(), $currentElement);
                break;

            default:
                break;
        }
        $this->readerPool->interpret($readerContext, $currentElement);
        return $this;
    }

    /**
     * Merge Container attributes
     *
     * @param \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Layout\Element $currentElement
     * @return void
     */
    protected function mergeContainerAttributes(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement
    ) {
        $containerName = $currentElement->getAttribute('name');
        $elementData = $scheduledStructure->getStructureElementData($containerName);

        if (isset($elementData['attributes'])) {
            $keys = array_keys($elementData['attributes']);
            foreach ($keys as $key) {
                if (isset($currentElement[$key])) {
                    $elementData['attributes'][$key] = (string)$currentElement[$key];
                }
            }
        } else {
            $elementData['attributes'] = [
                self::CONTAINER_OPT_HTML_TAG   => (string)$currentElement[self::CONTAINER_OPT_HTML_TAG],
                self::CONTAINER_OPT_HTML_ID    => (string)$currentElement[self::CONTAINER_OPT_HTML_ID],
                self::CONTAINER_OPT_HTML_CLASS => (string)$currentElement[self::CONTAINER_OPT_HTML_CLASS],
                self::CONTAINER_OPT_LABEL      => (string)$currentElement[self::CONTAINER_OPT_LABEL],
            ];
        }
        $scheduledStructure->setStructureElementData($containerName, $elementData);
    }
}
