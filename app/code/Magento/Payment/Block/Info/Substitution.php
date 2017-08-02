<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Info;

/**
 * Substitution payment info
 * @since 2.0.0
 */
class Substitution extends \Magento\Payment\Block\Info
{
    /**
     * Add additional info block
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $parentBlock = $this->getParentBlock();
        if (!$parentBlock) {
            return $this;
        }

        $container = $parentBlock->getParentBlock();
        if ($container) {
            $block = $this->_layout->createBlock(
                \Magento\Framework\View\Element\Template::class,
                '',
                ['data' => ['method' => $this->getMethod(), 'template' => 'Magento_Payment::info/substitution.phtml']]
            );
            $container->setChild('order_payment_additional', $block);
        }
        return $this;
    }
}
