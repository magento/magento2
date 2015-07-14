<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Customer\Autentication;

class ConfigPlugin
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(\Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Customer\Block\Account\AuthenticationPopup $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(
        \Magento\Customer\Block\Account\AuthenticationPopup $subject,
        array  $result
    ) {
        $result['checkoutUrl'] = $this->urlBuilder->getUrl('checkout');
        return $result;
    }
}
