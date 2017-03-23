<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Magento translate adapter
 */
namespace Magento\Framework\Translate;

class Adapter extends AbstractAdapter
{
    /**
     * Translate message string.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param array|string $messageId
     * @param null|string $locale
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        $translator = $this->getOptions('translator');
        if (is_callable($translator)) {
            return call_user_func($translator, $messageId);
        } else {
            return $messageId;
        }
    }

    /**
     * Translate message string.
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $messageId = array_shift($args);
        $string = $this->translate($messageId);
        if (count($args) > 0) {
            $string = vsprintf($string, $args);
        }
        return $string;
    }
}
