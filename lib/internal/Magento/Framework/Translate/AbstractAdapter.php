<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento translate abstract adapter
 */
namespace Magento\Framework\Translate;

use Laminas\I18n\View\Helper\AbstractTranslatorHelper;

abstract class AbstractAdapter extends AbstractTranslatorHelper implements AdapterInterface
{
    /**
     * Load translation data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param mixed $data
     * @param string $locale
     * @param array $options (optional)
     * @return array
     */
    protected function _loadTranslationData($data, $locale, array $options = [])
    {
        return [];
    }

    /**
     * Is translation available.
     *
     * Return false, as \Laminas\Validator\ValidatorChain pass message into translator only when isTranslated is false
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $messageId
     * @param bool $original
     * @param string|null $locale
     * @return false
     */
    public function isTranslated($messageId, $original = false, $locale = null)
    {
        return false;
    }

    /**
     * Stub for setLocale functionality
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        return $this;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return \Magento\Framework\Translate\Adapter::class;
    }
}
