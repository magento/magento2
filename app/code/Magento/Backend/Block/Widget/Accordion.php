<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget;

/**
 * Magento_Backend accordion widget
 *
 * @api
 * @since 2.0.0
 */
class Accordion extends \Magento\Backend\Block\Widget
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $_items = [];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/accordion.phtml';

    /**
     * @return string[]
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param string $itemId
     * @param array $config
     * @return $this
     * @since 2.0.0
     */
    public function addItem($itemId, $config)
    {
        $this->_items[$itemId] = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Accordion\Item::class,
            $this->getNameInLayout() . '-' . $itemId
        )->setData(
            $config
        )->setAccordion(
            $this
        )->setId(
            $itemId
        );
        if (isset($config['content']) && $config['content'] instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $this->_items[$itemId]->setChild($itemId . '_content', $config['content']);
        }

        $this->setChild($itemId, $this->_items[$itemId]);
        return $this;
    }
}
