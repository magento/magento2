<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Translation\Model\Js;

/**
 * Provides translation data from a theme.
 *
 * @api
 * @since 100.0.2
 */
interface DataProviderInterface
{
    /**
     * Gets translation data for a given theme. Only returns phrases which are actually translated.
     *
     * @param string $themePath The path to the theme
     * @return array A string array where the key is the phrase and the value is the translated phrase.
     * @throws \Exception
     */
    public function getData($themePath);
}
