<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

/**
 * Grid widget massaction single action item
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 */
class Item extends \Magento\Backend\Block\Widget
{
    /**
     * @var Extended
     */
    protected $_massaction = null;

    /**
     * Set parent massaction block
     *
     * @param  Extended $massaction
     * @return $this
     */
    public function setMassaction($massaction)
    {
        $this->_massaction = $massaction;
        return $this;
    }

    /**
     * Retrieve parent massaction block
     *
     * @return Extended
     */
    public function getMassaction()
    {
        return $this->_massaction;
    }

    /**
     * Set additional action block for this item
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setAdditionalActionBlock($block)
    {
        if (is_string($block)) {
            $block = $this->getLayout()->createBlock($block);
        } elseif (is_array($block)) {
            $block = $this->_createFromConfig($block);
        } elseif (!$block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown block type'));
        }

        $this->setChild('additional_action', $block);
        return $this;
    }

    /**
     * @param array $config
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _createFromConfig(array $config)
    {
        $type = isset($config['type']) ? $config['type'] : 'default';
        switch ($type) {
            default:
                $blockClass = \Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional\DefaultAdditional::class;
                break;
        }

        $block = $this->getLayout()->createBlock($blockClass);
        $block->createFromConfiguration(isset($config['type']) ? $config['config'] : $config);
        return $block;
    }

    /**
     * Retrieve additional action block for this item
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function getAdditionalActionBlock()
    {
        return $this->getChildBlock('additional_action');
    }

    /**
     * Retrieve additional action block HTML for this item
     *
     * @return string
     */
    public function getAdditionalActionBlockHtml()
    {
        return $this->getChildHtml('additional_action');
    }
}
