<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento translate abstract adapter
 */
namespace Magento\Framework\Translate;

abstract class AbstractAdapter extends \Zend_Translate_Adapter implements AdapterInterface
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
     * Return false, as \Zend_Validate pass message into translator only when isTranslated is false
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $messageId
     * @param bool $original
     * @param null $locale
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
