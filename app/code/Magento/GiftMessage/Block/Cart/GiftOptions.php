<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Cart;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\Encoder;
use Magento\GiftMessage\Model\CompositeConfigProvider;

/**
 * @api
 * @since 100.0.2
 */
class GiftOptions extends \Magento\Backend\Block\Template
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
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

    /**
     * @var Encoder
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param Encoder $jsonEncoder
     * @param CompositeConfigProvider $configProvider
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        Encoder $jsonEncoder,
        CompositeConfigProvider $configProvider,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->configProvider = $configProvider;
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return $this->jsonEncoder->encode($this->jsLayout);
    }

    /**
     * Retrieve gift message configuration
     *
     * @return array
     */
    public function getGiftOptionsConfigJson()
    {
        return $this->jsonEncoder->encode($this->configProvider->getConfig());
    }
}
