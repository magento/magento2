<?php
/**
 * Config sections list. Used to cache/read config sections separately.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Config_Sections
{
    /**
     * Instructions for spitting config cache
     * array(
     *      $sectionName => $recursionLevel
     * )
     * Recursion level provides availability to cache subnodes separatly
     *
     * @var array
     */
    protected $_sections = array(
        'admin'     => 0,
        'adminhtml' => 0,
        'crontab'   => 0,
        'install'   => 0,
        'stores'    => 1,
        'websites'  => 0
    );

    /**
     * Retrieve sections
     *
     * @return array
     */
    public function getSections()
    {
        return $this->_sections;
    }

    /**
     * Retrieve section cache key by path
     *
     * @param string $path
     * @return bool|string
     */
    public function getKey($path)
    {
        $pathParts = explode('/', $path);
        if (!array_key_exists($pathParts[0], $this->_sections)) {
            return false;
        }
        return implode('_', array_slice($pathParts, 0, $this->_sections[$pathParts[0]] + 1));
    }
}
