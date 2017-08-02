<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate;

/**
 * Magento translate adapter interface
 *
 * @api
 * @since 2.0.0
 */
interface AdapterInterface
{
    /**
     * Translate string
     *
     * @param string|array $messageId
     * @param null $locale
     * @return string
     * @since 2.0.0
     */
    public function translate($messageId, $locale = null);

    // @codingStandardsIgnoreStart
    /**
     * Translate string
     *
     * @return string
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 2.0.0
     */
    public function __();
    // @codingStandardsIgnoreEnd
}
