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

final class Mage_Connect_Command_Registry
extends Mage_Connect_Command
{
    const PACKAGE_PEAR_DIR = 'pearlib/php/.registry';

    /**
     * List-installed callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doList($command, $options, $params)
    {
        $this->cleanupParams($params);
        try {
            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $ftpObj) = $packager->getRemoteCache($ftp);
            } else {
                $cache = $this->getSconfig();
            }
            if(!empty($params[0])) {
                $chanName = $conf->chanName($params[0]);
                $data = $cache->getInstalledPackages($chanName);
            } else {
                $data = $cache->getInstalledPackages();
            }
            if($ftp) {
                @unlink($cache->getFilename());
            }
            $this->ui()->output(array($command=>array('data'=>$data, 'channel-title'=>"Installed package for channel '%s' :")));
        } catch (Exception $e) {
            if($ftp) {
                @unlink($cache->getFilename());
            }
            $this->doError($command, $e->getMessage());
        }

    }

    /**
     * list-files callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doFileList($command, $options, $params)
    {
        $this->cleanupParams($params);
        //$this->splitPackageArgs($params);
        try {
            $channel = false;
            if(count($params) < 2) {
                throw new Exception("Argument count should be = 2");
            }
            $channel = $params[0];
            $package = $params[1];

            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $cache = $this->getSconfig();
                $confif = $this->config();
            }
            if(!$cache->hasPackage($channel, $package)) {
                return $this->ui()->output("No package found: {$channel}/{$package}");
            }

            $p = $cache->getPackageObject($channel, $package);
            $contents = $p->getContents();
            if($ftp) {
                $ftpObj->close();
            }
            if(!count($contents)) {
                return $this->ui()->output("No contents for package {$package}");
            }
            $title = ("Contents of '{$package}': ");
            if($ftp) {
                @unlink($config->getFilename());
                @unlink($cache->getFilename());
            }

            $this->ui()->output(array($command=>array('data'=>$contents, 'title'=>$title)));

        } catch (Exception $e) {
            if($ftp) {
                @unlink($config->getFilename());
                @unlink($cache->getFilename());
            }
            $this->doError($command, $e->getMessage());
        }

    }

    /**
     * Installed package info
     * info command callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return
     */
    public function doInfo($command, $options, $params)
    {
        $this->cleanupParams($params);
        //$this->splitPackageArgs($params);

        $cache = null;
        $ftp = empty($options['ftp']) ? false : $options['ftp'];
        try {
            $channel = false;
            if(count($params) < 2) {
                throw new Exception("Argument count should be = 2");
            }
            $channel = $params[0];
            $package = $params[1];
            $packager = $this->getPackager();
            if($ftp) {
                list($cache, $ftpObj) = $packager->getRemoteCache($ftp);
            } else {
                $cache = $this->getSconfig();
            }

            if(!$cache->isChannel($channel)) {
                throw new Exception("'{$channel}' is not a valid installed channel name/uri");
            }
            $channelUri = $cache->chanUrl($channel);
            $rest = $this->rest();
            $rest->setChannel($channelUri);
            $releases = $rest->getReleases($package);
            if(false === $releases) {
                throw new Exception("No information found about {$channel}/{$package}");
            }
            $data = array($command => array('releases'=>$releases));
            if($ftp) {
                @unlink($cache->getFilename());
            }
            $this->ui()->output($data);
        } catch (Exception $e) {
            if ($ftp && isset($cache)) {
                @unlink($cache->getFilename());
            }
            $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Synchronize manually installed package info with local cache
     *
     * @param string $command
     * @param array $options
     * @param array $params
     */
    public function doSync($command, $options, $params)
    {
        $this->cleanupParams($params);
        try {
            $packager = $this->getPackager();
            $cache = null;
            $config = null;
            $ftpObj = null;
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $config = $this->config();
                $cache = $this->getSconfig();
            }
            if ($this->_checkPearData($config)) {
                $this->doSyncPear($command, $options, $params);
            }

            $packageDir = $config->magento_root . DS . Mage_Connect_Package::PACKAGE_XML_DIR;
            if (is_dir($packageDir)) {
                $entries = scandir($packageDir);
                foreach ((array)$entries as $entry) {
                    $path =  $packageDir. DS .$entry;
                    $info = pathinfo($path);
                    if ($entry == '.' || $entry == '..' || is_dir($path) || $info['extension'] != 'xml') {
                        continue;
                    }

                    if (is_readable($path)) {
                        $data = file_get_contents($path);
                        if ($data === false) {
                            continue;
                        }

                        $package = new Mage_Connect_Package($data);
                        $name = $package->getName();
                        $channel = $package->getChannel();
                        $version = $package->getVersion();
                        if (!$cache->isChannel($channel) && $channel == $config->root_channel) {
                            $cache->addChannel($channel, $config->root_channel_uri);
                        }
                        if (!$cache->hasPackage($channel, $name, $version, $version)) {
                            $cache->addPackage($package);
                            $this->ui()->output("Successfully added: {$channel}/{$name}-{$version}");
                        }
                    }
                }
                if ($ftp) {
                    $packager->writeToRemoteCache($cache, $ftpObj);
                }
            }
        } catch (Exception $e) {
            $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Synchronize packages installed earlier (by pear installer) with local cache
     *
     * @param string $command
     * @param array $options
     * @param array $params
     */
    public function doSyncPear($command, $options, $params)
    {
        $this->cleanupParams($params);
        try {
            $packager = $this->getPackager();
            $cache = null;
            $config = null;
            $ftpObj = null;
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $config = $this->config();
                $cache = $this->getSconfig();
            }

            $pkglist = array();
            if (!$this->_checkPearData($config)) {
                return $pkglist;
            }

            $pearStorage = $config->magento_root . DS . $config->downloader_path . DS . self::PACKAGE_PEAR_DIR;
            $channels = array(
                '.channel.connect.magentocommerce.com_community',
                '.channel.connect.magentocommerce.com_core'
            );
            foreach ($channels as $channel) {
                $channelDirectory = $pearStorage . DS . $channel;
                if (!file_exists($channelDirectory) || !is_dir($channelDirectory)) {
                    continue;
                }

                $dp = opendir($channelDirectory);
                if (!$dp) {
                    continue;
                }

                while ($ent = readdir($dp)) {
                    if ($ent{0} == '.' || substr($ent, -4) != '.reg') {
                        continue;
                    }
                    $pkglist[] = array('file'=>$ent, 'channel'=>$channel);
                }
                closedir($dp);
            }

            $package = new Mage_Connect_Package();
            foreach ($pkglist as $pkg) {
                $pkgFilename = $pearStorage . DS . $pkg['channel'] . DS . $pkg['file'];
                if (!file_exists($pkgFilename)) {
                    continue;
                }
                $data = file_get_contents($pkgFilename);
                $data = unserialize($data);

                $package->importDataV1x($data);
                $name = $package->getName();
                $channel = $package->getChannel();
                $version = $package->getVersion();
                if (!$cache->isChannel($channel) && $channel == $config->root_channel) {
                    $cache->addChannel($channel, $config->root_channel_uri);
                }
                if (!$cache->hasPackage($channel, $name, $version, $version)) {
                    $cache->addPackage($package);

                    if($ftp) {
                        $localXml = tempnam(sys_get_temp_dir(),'package');
                        @file_put_contents($localXml, $package->getPackageXml());
                        
                        if (is_file($localXml)) {
                            $ftpDir = $ftpObj->getcwd();
                            $remoteXmlPath = $ftpDir . '/' . Mage_Connect_Package::PACKAGE_XML_DIR;
                            $remoteXml = $package->getReleaseFilename() . '.xml';
                            $ftpObj->mkdirRecursive($remoteXmlPath);
                            $ftpObj->upload($remoteXml, $localXml, 0777, 0666);
                            $ftpObj->chdir($ftpDir);
                        }
                    } else {
                        $destDir = rtrim($config->magento_root, "\\/") . DS . Mage_Connect_Package::PACKAGE_XML_DIR;
                        $destFile = $package->getReleaseFilename() . '.xml';
                        $dest = $destDir . DS . $destFile;

                        @mkdir($destDir, 0777, true);
                        @file_put_contents($dest, $package->getPackageXml());
                        @chmod($dest, 0666);
                    }

                    $this->ui()->output("Successfully added: {$channel}/{$name}-{$version}");
                }

            }

            $config->sync_pear = true;
            if($ftp) {
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            }
        } catch (Exception $e) {
            $this->doError($command, $e->getMessage());
        }
        
        return true;
    }

    /**
     * Check is need to sync old pear data
     * 
     * @param Mage_Connect_Config $config
     * @return boolean
     */
    protected function _checkPearData($config) {
        $pearStorage = $config->magento_root . DS . $config->downloader_path  . DS . self::PACKAGE_PEAR_DIR;
        return (!$config->sync_pear) && file_exists($pearStorage) && is_dir($pearStorage);
    }

}
