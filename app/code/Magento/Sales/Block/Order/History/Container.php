<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Order\History;

/**
 * Sales order history extra container block
 *
 * @api
 * @since 100.1.1
 */
class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;

    /**
     * Set order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return $this
     * @since 100.1.1
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function getOrder()
    {
        return $this->order;
    }

    /**
     * Here we set an order for children during retrieving their HTML
     *
     * @param string $alias
     * @param bool $useCache
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.1.1
     */
    public function getChildHtml($alias = '', $useCache = false)
    {
        $layout = $this->getLayout();
        if ($layout) {
            $name = $this->getNameInLayout();
            foreach ($layout->getChildBlocks($name) as $child) {
                $child->setOrder($this->getOrder());
            }
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
