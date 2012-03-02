<?php
/**
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
 * @category    Mage
 * @package     Mage_Media
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Media library image config interface
 *
 * @category   Mage
 * @package    Mage_Media
 * @author      Magento Core Team <core@magentocommerce.com>
 */
interface Mage_Media_Model_Image_Config_Interface
{

    /**
     * Retrive base url for media files
     *
     * @return string
     */
    function getBaseMediaUrl();

    /**
     * Retrive base path for media files
     *
     * @return string
     */
    function getBaseMediaPath();

    /**
     * Retrive url for media file
     *
     * @param string $file
     * @return string
     */
    function getMediaUrl($file);

    /**
     * Retrive file system path for media file
     *
     * @param string $file
     * @return string
     */
    function getMediaPath($file);

}
