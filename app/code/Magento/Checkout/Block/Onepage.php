<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

/**
 * Onepage checkout block
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Onepage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     * @since 2.0.0
     */
    protected $formKey;

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
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     * @since 2.0.0
     */
    protected $configProvider;

    /**
     * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface[]
     * @since 2.0.0
     */
    protected $layoutProcessors;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param array $layoutProcessors
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        array $layoutProcessors = [],
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->configProvider = $configProvider;
        $this->layoutProcessors = $layoutProcessors;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return $this->serializer->serialize($this->jsLayout);
    }

    /**
     * Retrieve form key
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Get base url for block.
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return bool|string
     * @since 2.2.0
     */
    public function getSerializedCheckoutConfig()
    {
        return $this->serializer->serialize($this->getCheckoutConfig());
    }
}
