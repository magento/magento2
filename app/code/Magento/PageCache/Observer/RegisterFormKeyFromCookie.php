<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\PageCache\Observer\RegisterFormKeyFromCookie
 *
 * @since 2.0.0
 */
class RegisterFormKeyFromCookie implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\PageCache\FormKey
     * @since 2.0.0
     */
    private $cookieFormKey;

    /**
     * @var \Magento\Framework\Escaper
     * @since 2.0.0
     */
    private $escaper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     * @since 2.0.0
     */
    private $sessionFormKey;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     * @since 2.0.0
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     * @since 2.0.0
     */
    private $sessionConfig;

    /**
     * @param \Magento\Framework\App\PageCache\FormKey $formKey
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Data\Form\FormKey $sessionFormKey
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\PageCache\FormKey $formKey,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Data\Form\FormKey $sessionFormKey,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
    ) {
        $this->cookieFormKey = $formKey;
        $this->escaper = $escaper;
        $this->sessionFormKey = $sessionFormKey;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * Register form key in session from cookie value
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->cookieFormKey->get()) {
            $this->updateCookieFormKey($this->cookieFormKey->get());

            $this->sessionFormKey->set(
                $this->escaper->escapeHtml($this->cookieFormKey->get())
            );
        }
    }

    /**
     * @param string $formKey
     * @return void
     * @since 2.0.0
     */
    private function updateCookieFormKey($formKey)
    {
        $cookieMetadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata();
        $cookieMetadata->setDomain($this->sessionConfig->getCookieDomain());
        $cookieMetadata->setPath($this->sessionConfig->getCookiePath());
        $cookieMetadata->setDuration($this->sessionConfig->getCookieLifetime());

        $this->cookieFormKey->set(
            $formKey,
            $cookieMetadata
        );
    }
}
