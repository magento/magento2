<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Observer;

class RegisterFormKeyFromCookie
{
    /**
     * @var \Magento\Framework\App\PageCache\FormKey
     */
    private $cookieFormKey;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $session;

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
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Data\Form\FormKey $sessionFormKey
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     */
    public function __construct(
        \Magento\Framework\App\PageCache\FormKey $formKey,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Data\Form\FormKey $sessionFormKey,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
    ) {
        $this->session = $session;
        $this->cookieFormKey = $formKey;
        $this->escaper = $escaper;
        $this->sessionFormKey = $sessionFormKey;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * Register form key in session from cookie value
     *
     * @return void
     */
    public function execute()
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
        $this->cookieFormKey->set(
            $formKey,
            $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDomain($this->sessionConfig->getCookieDomain())
                ->setPath($this->sessionConfig->getCookiePath())
                ->setDuration($this->sessionConfig->getCookieLifetime())
        );
    }
}
