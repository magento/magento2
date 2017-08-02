<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order;

/**
 * Sales order link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_registry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_registry = $registry;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    private function getOrder()
    {
        return $this->_registry->registry('current_order');
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @since 2.0.0
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath(), ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if ($this->hasKey()
            && method_exists($this->getOrder(), 'has' . $this->getKey())
            && !$this->getOrder()->{'has' . $this->getKey()}()
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
