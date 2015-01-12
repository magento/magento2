<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento translate adapter interface
 */
namespace Magento\Framework\Translate;

interface AdapterInterface
{
    /**
     * Translate string
     *
     * @param string|array $messageId
     * @param null $locale
     * @return string
     */
    public function translate($messageId, $locale = null);

    /**
     * Translate string
     *
     * @return string
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function __();
}
