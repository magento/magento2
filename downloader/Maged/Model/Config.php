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
 * Class config
 *
 * @category   Mage
 * @package    Mage_Connect
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Maged_Model_Config extends Maged_Model_Config_Abstract
{
    /**
     * Get channel config class
     * @return Maged_Model_Config_Interface
     */
    public function getChannelConfig()
    {
        $this->load();
        $channel = trim($this->get('root_channel'));
        if (!empty($channel)) {
            try {
                return $this->controller()->model('config_'.$channel, true);
            } catch (Exception $e) {
                throw new Exception('Not valid config.ini file.');
            }
        } else {
            throw new Exception('Not valid config.ini file.');
        }
    }

    /**
    * Save post data to config
    *
    * @param array $p
    * @return Maged_Model_Config
    */
    public function saveConfigPost($p)
    {
        $configParams = array(
            'protocol',
            'preferred_state',
            'use_custom_permissions_mode',
            'mkdir_mode',
            'chmod_file_mode',
            'magento_root',
            'downloader_path',
            'root_channel_uri',
            'root_channel',
            'ftp',
        );
        foreach ($configParams as $paramName){
            if (isset($p[$paramName])) {
               $this->set($paramName, $p[$paramName]);
            }
        }
        $this->save();
        return $this;
    }
}
