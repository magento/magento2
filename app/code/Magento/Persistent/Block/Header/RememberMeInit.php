<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Block\Header;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Persistent\Model\CheckoutConfigProvider;

class RememberMeInit extends Template
{
    /**
     * @param Context $context
     * @param array $data
     * @param SerializerInterface|null $serializer
     * @param CheckoutConfigProvider|null $checkoutConfigProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context                         $context,
        array                           $data = [],
        private ?SerializerInterface    $serializer = null,
        private ?CheckoutConfigProvider $checkoutConfigProvider = null
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(JsonHexTag::class);
        $this->checkoutConfigProvider = $checkoutConfigProvider ?: ObjectManager::getInstance()
            ->get(CheckoutConfigProvider::class);
    }

    /**
     * Retrieve serialized config.
     *
     * @return string|bool
     */
    private function getSerializedCheckoutConfig(): string|bool
    {
        return $this->serializer->serialize($this->checkoutConfigProvider->getConfig());
    }

    /**
     * @inheritDoc
     */
    public function toHtml()
    {
        $html = parent::toHtml();
        $serializedConfig = $this->getSerializedCheckoutConfig();
        $jsString = '<script type="text/x-magento-init">{"*":
            {"Magento_Persistent/js/remember-me-config": {
            "config": ' . $serializedConfig . '
            }}}</script>';

        $html .= $jsString;
        return $html;
    }
}
