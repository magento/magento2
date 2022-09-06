<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate;

use Laminas\Validator\Translator\TranslatorInterface;

/**
 * Magento translate adapter interface
 *
 * @api
 * @since 100.0.2
 */
interface AdapterInterface extends TranslatorInterface
{
    /**
     * Translate string
     *
     * @param string|array $messageId
     * @param string $textDomain
     * @param string|null $locale
     * @return string
     */
    public function translate($messageId, $textDomain = 'default', $locale = null);

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
