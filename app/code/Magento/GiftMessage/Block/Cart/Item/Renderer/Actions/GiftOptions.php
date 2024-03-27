<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\Json\Encoder;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 * @since 100.0.2
 */
class GiftOptions extends Generic
{
    /**
     * @var bool
     */
    protected $_isScopePrivate = false;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var array|LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

    /**
     * @var Encoder
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param Encoder $jsonEncoder
     * @param array $layoutProcessors
     * @param array $data
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
