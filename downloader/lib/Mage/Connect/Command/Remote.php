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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

final class Mage_Connect_Command_Remote
extends Mage_Connect_Command
{

    /**
     * List-upgrades callback
     * @param srting $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doListUpgrades($command, $options, $params)
    {

        $this->cleanupParams($params);
        try {
            $packager = new Mage_Connect_Packager();
            $channelAuth = isset($options['auth'])?$options['auth']:array();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $cache = $this->getSconfig();
                $config = $this->config();
            }

            if(!empty($params[0])) {
                $channels = $params[0];
                $cache->getChannel($channels);
            } else {
                $channels = $cache->getChannelNames();
            }
            $rest = $this->rest();
            if(!empty($channelAuth)){
                $rest->getLoader()->setCredentials($channelAuth['username'], $channelAuth['password']);
            }
            $ups = $packager->getUpgradesList($channels, $cache, $config, $rest);

            if(count($ups)) {
                $data = array($command => array('data'=>$ups));
            } else {
                $data = "No upgrades available";
            }
            $this->ui()->output($data);
        } catch(Exception $e) {
            $this->doError($command, $e->getMessage());
        }
    }


    /**
     * List available
     * @param $command
     * @param $options
     * @param $params
     * @return unknown_type
     */

    public function doListAvailable($command, $options, $params)
    {
        $this->cleanupParams($params);

        try {
            $packager = new Mage_Connect_Packager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $cache = $this->getSconfig();
                $config = $this->config();
            }

            if(!empty($params[0])) {
                $channels = array($params[0]);
                $cache->getChannel($channels[0]);
            } else {
                $channels =  $cache->getChannelNames();
            }
            
            

            $packs = array();
            foreach ($channels as $channel) {
                try {
                    $chan = $cache->getChannel($channel);
                    $uri = $cache->chanUrl($channel);

                    $rest = $this->rest();
                    $rest->setChannel($uri);

                    $packages = $rest->getPackages();
                    if(!count($packages)) {
                        $this->ui()->output("Channel '{$channel}' has no packages");
                        continue;
                    }
                    $packs[$channel]['title'] = "Packages for channel '".$channel."':";
                    foreach($packages as $p) {
                        $packageName = $p['n'];
                        $releases = array();
                        foreach($p['r'] as $k=>$r) {
                            $releases[$r] = $rest->shortStateToLong($k);
                        }
                        $packs[$channel]['packages'][$packageName]['releases'] = $releases;
                    }
                } catch (Exception $e) {
                    $this->doError($command, $e->getMessage());
                }
            }
            $dataOut = array();
            $dataOut[$command]= array('data'=>$packs);
            $this->ui()->output($dataOut);

        } catch(Exception $e) {
            $this->doError($command, $e->getMessage());
        }
         
    }

    /**
     * Download command callback
     *
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doDownload($command, $options, $params)
    {
        $this->cleanupParams($params);
        //$this->splitPackageArgs($params);
        try {
            if(count($params) < 2) {
                throw new Exception("Arguments should be: channel Package");
            }

            $channel = $params[0];
            $package = $params[1];

            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $cache = $this->getSconfig();
                $config = $this->config();
            }

            $chan = $cache->getChannel($channel);
            $uri = $cache->chanUrl($channel);

            $rest = $this->rest();
            $rest->setChannel($uri);
            $c = $rest->getReleases($package);
            if(!count($c)) {
                throw new Exception("No releases found for package");
            }
            $version = $cache->detectVersionFromRestArray($c);
            $dir = $config->getChannelCacheDir($channel);
            $file = $dir.DIRECTORY_SEPARATOR.$package."-".$version.".tgz";
            $rest->downloadPackageFileOfRelease($package, $version, $file);
            if($ftp) {
                @unlink($config->getFilename());
                @unlink($cache->getFilename());
            }
            $this->ui()->output("Saved to: ". $file);
        } catch (Exception $e) {
            if($ftp) {
                @unlink($config->getFilename());
                @unlink($cache->getFilename());
            }
            $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Clear cache command callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doClearCache($command, $options, $params)
    {
        $this->cleanupParams($params);
        try {
            $packager = new Mage_Connect_Packager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $ftpObj) = $packager->getRemoteCache($ftp);
                $cache->clear();
                $packager->writeToRemoteCache($cache, $ftpObj);              
            } else {
                $cache = $this->getSconfig();
                $cache->clear();
            }
        } catch (Exception $e) {
             $this->doError($command, $e->getMessage());
        }
    }





}
