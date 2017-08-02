<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

/**
 * Class \Magento\Framework\Composer\MagentoComponent
 *
 */
class MagentoComponent
{
    /**
     * Get matched Magento component or empty array, if it's not a Magento component
     *
     * @param string $key
     * @return string[] ['type' => '<type>', 'area' => '<area>', 'name' => '<name>']
     *             Ex.: ['type' => 'module', 'name' => 'catalog']
     *                  ['type' => 'theme', 'area' => 'frontend', 'name' => 'blank']
     */
    public static function matchMagentoComponent($key)
    {
        $typePattern = 'module|theme|language|framework';
        $areaPattern = 'frontend|adminhtml';
        $namePattern = '[a-z0-9_-]+';
        $regex = '/^magento\/(?P<type>' . $typePattern . ')(?:-(?P<area>' . $areaPattern . '))?(?:-(?P<name>'
            . $namePattern . '))?$/';
        if (preg_match($regex, $key, $matches)) {
            return $matches;
        }
        return [];
    }
}
