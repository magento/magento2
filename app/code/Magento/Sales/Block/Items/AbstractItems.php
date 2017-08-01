<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Items;

/**
 * Abstract block for display sales (quote/order/invoice etc.) items
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
class AbstractItems extends \Magento\Framework\View\Element\Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * Retrieve item renderer block
     *
     * @param string $type
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function getItemRenderer($type)
    {
        /** @var \Magento\Framework\View\Element\RendererList $rendererList */
        $rendererList = $this->getRendererListName() ? $this->getLayout()->getBlock(
            $this->getRendererListName()
        ) : $this->getChildBlock(
            'renderer.list'
        );
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        $renderer = $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
        $renderer->setRenderedBlock($this);
        return $renderer;
    }

    /**
     * Prepare item before output
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $renderer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        return $this;
    }

    /**
     * Return product type for quote/order item
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @since 2.0.0
     */
    protected function _getItemType(\Magento\Framework\DataObject $item)
    {
        if ($item->getOrderItem()) {
            $type = $item->getOrderItem()->getProductType();
        } elseif ($item instanceof \Magento\Quote\Model\Quote\Address\Item) {
            $type = $item->getQuoteItem()->getProductType();
        } else {
            $type = $item->getProductType();
        }
        return $type;
    }

    /**
     * Get item row html
     *
     * @param   \Magento\Framework\DataObject $item
     * @return  string
     * @since 2.0.0
     */
    public function getItemHtml(\Magento\Framework\DataObject $item)
    {
        $type = $this->_getItemType($item);

        $block = $this->getItemRenderer($type)->setItem($item);
        $this->_prepareItem($block);
        return $block->toHtml();
    }
}
