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
 */
class RegisterFormKeyFromCookie implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\PageCache\FormKey
     */
    private $cookieFormKey;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $sessionFormKey;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    private $sessionConfig;

    /**
     * @param \Magento\Framework\App\PageCache\FormKey $formKey
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Data\Form\FormKey $sessionFormKey
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
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
     */
    private function updateCookieFormKey($formKey)
    {
        $cookieMetadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata();
        $cookieMetadata->setDomain($this->sessionConfig->getCookieDomain());
        $cookieMetadata->setPath($this->sessionConfig->getCookiePath());
        $lifetime = $this->sessionConfig->getCookieLifetime();
        if ($lifetime !== 0) {
            $cookieMetadata->setDuration($lifetime);
        }

        $this->cookieFormKey->set(
            $formKey,
            $cookieMetadata
        );
    }
}
