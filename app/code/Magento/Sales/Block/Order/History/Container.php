<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\History;

/**
 * Sales order history extra container block
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
