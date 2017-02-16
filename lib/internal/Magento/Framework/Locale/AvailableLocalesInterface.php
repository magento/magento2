<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

use Magento\Framework\View\DesignInterface;

/**
 * Interface for classes that fetching available locale list for the concrete theme.
 */
interface AvailableLocalesInterface
{
    /**
     * Returns list of generated locales for theme by given theme code and area.
     *
     * @param string $code theme code identifier
     * @param string $area area in which theme can be applied
     * @return array of locales codes, for example: ['en_US', 'en_GB']
     */
    public function getList($code, $area = DesignInterface::DEFAULT_AREA);
}
