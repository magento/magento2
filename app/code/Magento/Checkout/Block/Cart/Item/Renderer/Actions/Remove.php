<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Helper\Cart;
use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 2.0.0
 */
class Remove extends Generic
{
    /**
     * @var Cart
     * @since 2.0.0
     */
    protected $cartHelper;

    /**
     * @param Template\Context $context
     * @param Cart $cartHelper
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        Cart $cartHelper,
        array $data = []
    ) {
        $this->cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get delete item POST JSON
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getDeletePostJson()
    {
        return $this->cartHelper->getDeletePostJson($this->getItem());
    }
}
