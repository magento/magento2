<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate;

/**
 * Magento translate adapter interface
 *
 * @api
 */
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

    // @codingStandardsIgnoreStart
    /**
     * Translate string
     *
     * @return string
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function __();
    // @codingStandardsIgnoreEnd
}
