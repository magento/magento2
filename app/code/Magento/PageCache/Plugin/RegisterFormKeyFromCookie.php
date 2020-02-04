<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Plugin;

use Magento\Framework\App\PageCache\FormKey as CacheFormKey;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Escaper;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

/**
 * Allow for registration of a form key through cookies.
 */
class RegisterFormKeyFromCookie
{
    /**
     * @var CacheFormKey
     */
    private $cookieFormKey;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var ConfigInterface
     */
    private $sessionConfig;

    /**
     * @param CacheFormKey $cacheFormKey
     * @param Escaper $escaper
     * @param FormKey $formKey
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param ConfigInterface $sessionConfig
     */
    public function __construct(
        CacheFormKey $cacheFormKey,
        Escaper $escaper,
        FormKey $formKey,
        CookieMetadataFactory $cookieMetadataFactory,
        ConfigInterface $sessionConfig
    ) {
        $this->cookieFormKey = $cacheFormKey;
        $this->escaper = $escaper;
        $this->formKey = $formKey;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * Set form key from the cookie.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(): void
    {
        if ($this->cookieFormKey->get()) {
            $this->updateCookieFormKey($this->cookieFormKey->get());

            $this->formKey->set(
                $this->escaper->escapeHtml($this->cookieFormKey->get())
            );
        }
    }

    /**
     * Set form key cookie
     *
     * @param string $formKey
     * @return void
     */
    private function updateCookieFormKey(string $formKey): void
    {
        $cookieMetadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata();
        $cookieMetadata->setDomain($this->sessionConfig->getCookieDomain());
        $cookieMetadata->setPath($this->sessionConfig->getCookiePath());
        $cookieMetadata->setSecure($this->sessionConfig->getCookieSecure());
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
