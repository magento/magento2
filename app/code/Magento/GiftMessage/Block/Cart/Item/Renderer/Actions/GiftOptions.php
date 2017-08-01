<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Cart\Item\Renderer\Actions;

use Magento\Backend\Block\Template\Context;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\Json\Encoder;

/**
 * @api
 * @since 2.0.0
 */
class GiftOptions extends Generic
{
    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_isScopePrivate = false;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $jsLayout;

    /**
     * @var array|LayoutProcessorInterface[]
     * @since 2.0.0
     */
    protected $layoutProcessors;

    /**
     * @var Encoder
     * @since 2.0.0
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param Encoder $jsonEncoder
     * @param array $layoutProcessors
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Encoder $jsonEncoder,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * Return JS layout
     *
     * @return string
     * @since 2.0.0
     */
    public function getJsLayout()
    {
        $jsLayout = $this->jsLayout;
        foreach ($this->layoutProcessors as $processor) {
            $jsLayout = $processor->process($jsLayout, $this->getItem());
        }
        return $this->jsonEncoder->encode($jsLayout);
    }
}
