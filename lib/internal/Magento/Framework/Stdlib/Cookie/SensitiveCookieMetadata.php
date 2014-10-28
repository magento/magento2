<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\StoreManagerInterface;

/**
 * Class SensitiveCookieMetadata
 *
 * The class has only methods extended from CookieMetadata
 * as path and domain are only data to be exposed by SensitiveCookieMetadata
 */
class SensitiveCookieMetadata extends CookieMetadata
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param array $metadata
     */
    public function __construct(StoreManagerInterface $storeManager, $metadata = [])
    {
        if (!isset($metadata[self::KEY_HTTP_ONLY])) {
            $metadata[self::KEY_HTTP_ONLY] = true;
        }
        $this->storeManager = $storeManager;
        parent::__construct($metadata);
    }


    /**
     * {@inheritdoc}
     */
    public function getSecure()
    {
        $this->updateSecureValue();
        return $this->get(self::KEY_SECURE);
    }

    /**
     * {@inheritdoc}
     */
    public function __toArray()
    {
        $this->updateSecureValue();
        return parent::__toArray();
    }

    /**
     * Update secure value, set it to store setting if it has no explicit value assigned.
     *
     * @return void
     */
    private function updateSecureValue()
    {
        if (null === $this->get(self::KEY_SECURE)) {
            $store = $this->storeManager->getStore();
            if (empty($store)) {
                $this->set(self::KEY_SECURE, true);
            } else {
                $this->set(self::KEY_SECURE, $store->isCurrentlySecure());
            }
        }
    }
}
