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
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Default helper of the module
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @param Magento_Filesystem $filesystem
     */
    public function __construct(Magento_Filesystem $filesystem)
    {
        $this->_filesystem = $filesystem;
    }

    /**
     * Retrieve file system path for local extension packages
     * Return path with last directory separator
     *
     * @return string
     */
    public function getLocalPackagesPath()
    {
        return Mage::getBaseDir('var') . DS . 'connect' . DS;
    }

    /**
     * Retrieve file system path for local extension packages (for version 1 packages only)
     * Return path with last directory separator
     *
     * @return string
     */
    public function getLocalPackagesPathV1x()
    {
        return Mage::getBaseDir('var') . DS . 'pear' . DS;
    }

    /**
     * Retrieve a map to convert a channel from previous version of Magento Connect Manager
     *
     * @return array
     */
    public function getChannelMapFromV1x()
    {
        return array(
            'connect.magentocommerce.com/community' => 'community',
            'connect.magentocommerce.com/core' => 'community'
        );
    }

    /**
     * Retrieve a map to convert a channel to previous version of Magento Connect Manager
     *
     * @return array
     */
    public function getChannelMapToV1x()
    {
        return array(
            'community' => 'connect.magentocommerce.com/community'
        );
    }

    /**
     * Convert package channel in order for it to be compatible with current version of Magento Connect Manager
     *
     * @param string $channel
     *
     * @return string
     */
    public function convertChannelFromV1x($channel)
    {
        $channelMap = $this->getChannelMapFromV1x();
        if (isset($channelMap[$channel])) {
            $channel = $channelMap[$channel];
        }
        return $channel;
    }

    /**
     * Convert package channel in order for it to be compatible with previous version of Magento Connect Manager
     *
     * @param string $channel
     *
     * @return string
     */
    public function convertChannelToV1x($channel)
    {
        $channelMap = $this->getChannelMapToV1x();
        if (isset($channelMap[$channel])) {
            $channel = $channelMap[$channel];
        }
        return $channel;
    }

    /**
     * Load local package data array
     *
     * @param string $packageName without extension
     * @return array|false
     */
    public function loadLocalPackage($packageName)
    {
        //check LFI protection
        $this->checkLfiProtection($packageName);

        $path = $this->getLocalPackagesPath();
        $xmlFile = $path . $packageName . '.xml';
        $serFile = $path . $packageName . '.ser';

        if ($this->_filesystem->isFile($xmlFile) && $this->_filesystem->isReadable($xmlFile)) {
            $xml  = simplexml_load_string($this->_filesystem->read($xmlFile));
            $data = Mage::helper('Mage_Core_Helper_Data')->xmlToAssoc($xml);
            if (!empty($data)) {
                return $data;
            }
        }

        if ($this->_filesystem->isFile($serFile) && $this->_filesystem->isReadable($xmlFile)) {
            $data = unserialize($this->_filesystem->read($serFile));
            if (!empty($data)) {
                return $data;
            }
        }

        return false;
    }
}
