<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\App\RequestInterface;

/**
 * Class SensitiveCookieMetadata
 *
 * The class has only methods extended from CookieMetadata
 * as path and domain are only data to be exposed by SensitiveCookieMetadata
 *
 * @api
 * @since 2.0.0
 */
class SensitiveCookieMetadata extends CookieMetadata
{
    /**
     * @var RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @param RequestInterface $request
     * @param array $metadata
     * @since 2.0.0
     */
    public function __construct(RequestInterface $request, $metadata = [])
    {
        if (!isset($metadata[self::KEY_HTTP_ONLY])) {
            $metadata[self::KEY_HTTP_ONLY] = true;
        }
        $this->request = $request;
        parent::__construct($metadata);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSecure()
    {
        $this->updateSecureValue();
        return $this->get(self::KEY_SECURE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function __toArray()
    {
        $this->updateSecureValue();
        return parent::__toArray();
    }

    /**
     * Update secure value, set it to request setting if it has no explicit value assigned.
     *
     * @return void
     * @since 2.0.0
     */
    private function updateSecureValue()
    {
        if (null === $this->get(self::KEY_SECURE)) {
            $this->set(self::KEY_SECURE, $this->request->isSecure());
        }
    }
}
