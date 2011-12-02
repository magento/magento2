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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

final class Mage_Connect_Command_Channels
extends Mage_Connect_Command
{

    /**
     * List available channels
     * @param $command
     * @param $params
     * @param $options
     */
    public function doList($command, $options, $params)
    {

        try {
            $title = "Available channels:";
            $aliasT = "Available aliases:";
            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
                $data = $cache->getData();
                @unlink($config->getFilename());
                @unlink($cache->getFilename());
            } else {
                $cache = $this->getSconfig();
                $config = $this->config();
                $data = $cache->getData();
            }            
            $out = array($command => array('data'=>$data, 'title'=>$title, 'title_aliases'=>$aliasT));
            $this->ui()->output($out);
        } catch (Exception $e) {
            $this->doError($command, $e->getMessage());
        }
    }

    /**
     * channel-delete callback method
     * @param string $command
     * @param array $options
     * @param array $params
     */
    public function doDelete($command, $options, $params)
    {
        $this->cleanupParams($params);
        try {
            if(count($params) != 1) {
                throw new Exception("Parameters count should be equal to 1");
            }
            $packager = $this->getPackager();

            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
                $cache->deleteChannel($params[0]);                
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            } else {
                $config = $this->config();
                $cache = $this->getSconfig();
                $cache->deleteChannel($params[0]);
            }
            $this->ui()->output("Successfully deleted");

        } catch (Exception $e) {
            $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Channel-add callback
     * @param string $command
     * @param array $options
     * @param array $params
     */
    public function doAdd($command, $options, $params)
    {
        $this->cleanupParams($params);
        try {
            if(count($params) != 1) {
                throw new Exception("Parameters count should be equal to 1");
            }
            $url = $params[0];
            $rest = $this->rest();
            $rest->setChannel($url);
            $data = $rest->getChannelInfo();
            $data->url = $url;
                        
            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                 list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
                 $cache->addChannel($data->name, $url);
                 $packager->writeToRemoteCache($cache, $ftpObj); 
                 @unlink($config->getFilename());                 
            } else {
                $cache = $this->getSconfig();               
                $config = $this->config();   
                $cache->addChannel($data->name, $url);
            }
            
            $this->ui()->output("Successfully added: ".$url);
        } catch (Exception $e) {
            $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Get information about given channel callback
     * @param string $command
     * @param array $options
     * @param array $params
     */
    public function doInfo($command, $options, $params)
    {

    }

    /**
     * channel-alias
     * @param $command
     * @param $options
     * @param $params
     * @return unknown_type
     */
    public function doAlias($command, $options, $params)
    {
        $this->cleanupParams($params);
        try {
            if(count($params) != 2) {
                throw new Exception("Parameters count should be equal to 2");
            }

            $packager = $this->getPackager();
            $chanUrl = $params[0];
            $alias = $params[1];            
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config,  $ftpObj) = $packager->getRemoteConf($ftp);
                $cache->addChannelAlias($chanUrl, $alias);
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            } else {                
                $cache = $this->getSconfig();
                $config = $this->config();
                $cache->addChannelAlias($chanUrl, $alias);                
            }
            $this->ui()->output("Successfully added: ".$alias);
        } catch (Exception $e) {
            $this->doError($command, $e->getMessage());
        }
    }

    public function doLogin($command, $options, $params)
    {

    }

    public function doLogout($command, $options, $params)
    {

    }

}
