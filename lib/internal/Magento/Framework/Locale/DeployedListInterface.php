<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Interface for classes that returning list of deployed locales.
 */
interface DeployedListInterface
{
    /**
     * Get options array of deployed locales for locale dropdown
     *
     * @return array
     */
    public function getLocales();

    /**
     * Get translated options array of deployed locales for locale dropdown
     *
     * @return array
     */
    public function getTranslatedLocales();
}
