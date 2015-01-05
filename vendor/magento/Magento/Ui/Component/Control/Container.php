<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Class Container
 */
class Container extends AbstractBlock
{
    /**
     * Default button class
     */
    const DEFAULT_BUTTON = 'Magento\Ui\Component\Control\Button';

    /**
     * Create button renderer
     *
     * @param string $blockName
     * @param string $blockClassName
     * @return \Magento\Ui\Component\Control\Button
     */
    protected function createButton($blockName, $blockClassName = null)
    {
        if (null === $blockClassName) {
            $blockClassName = static::DEFAULT_BUTTON;
        }

        return $this->getLayout()->createBlock($blockClassName, $blockName);
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var \Magento\Ui\Component\Control\Item $item */
        $item = $this->getButtonItem();
        $data = $item->getData();

        $contextPrefixName = $this->getData('context') ? ($this->getData('context')->getNameInLayout() . '-') : '';
        $block = $this->createButton(
            $contextPrefixName . $item->getId() . '-button',
            isset($data['class_name']) ? $data['class_name'] : null
        );
        $block->setData($data);
        return $block->toHtml();
    }
}
