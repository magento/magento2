<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Interface for classes that filtering locales by some condition.
 */
interface ListFilterInterface
{
    /**
     * Filter list of locales by some condition.
     *
     * @param array $locales list of locales for filtering
     * @return array  of filtered locales
     */
    public function filter(array $locales);
}
