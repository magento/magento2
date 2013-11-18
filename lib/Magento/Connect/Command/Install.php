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
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Connect\Command;

final class Install
extends \Magento\Connect\Command
{

    /**
     * Install action callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doInstall($command, $options, $params, $objects = array())
    {
        $this->cleanupParams($params);

        $installFileMode = $command === 'install-file';


         

        try {
            $packager = $this->getPackager();
            $forceMode = isset($options['force']);
            $upgradeAllMode = $command == 'upgrade-all';
            $upgradeMode = $command == 'upgrade' || $command == 'upgrade-all';
            $noFilesInstall = isset($options['nofiles']);
            $withDepsMode = !isset($options['nodeps']);
            $ignoreModifiedMode = true || !isset($options['ignorelocalmodification']);

            $rest = $this->rest();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $config = $this->config();
                $cache = $this->getSconfig();
            }
            if($installFileMode) {
                if(count($params) < 1) {
                    throw new \Exception("Argument should be: filename");
                }
                $filename = $params[0];
                if(!@file_exists($filename)) {
                    throw new \Exception("File '{$filename}' not found");
                }
                if(!@is_readable($filename)) {
                    throw new \Exception("File '{$filename}' is not readable");
                }

                $package = new \Magento\Connect\Package($filename);
                $package->validate();
                $errors = $package->getErrors();
                if(count($errors)) {
                    throw new \Exception("Package file is invalid\n".implode("\n", $errors));
                }

                $pChan = $package->getChannel();
                $pName = $package->getName();
                $pVer = $package->getVersion();


                if(!$cache->isChannel($pChan)) {
                    throw new \Exception("'{$pChan}' is not installed channel");
                }

                $conflicts = $cache->hasConflicts($pChan, $pName, $pVer);

                if(false !== $conflicts) {
                    $conflicts = implode(", ",$conflicts);
                    if($forceMode) {
                        $this->doError($command, "Package {$pChan}/{$pName} {$pVer} conflicts with: ".$conflicts);
                    } else {
                        throw new \Exception("Package {$pChan}/{$pName} {$pVer} conflicts with: ".$conflicts);
                    }
                }

                $conflicts = $package->checkPhpDependencies();
                if(true !== $conflicts) {
                    $confilcts = implode(",",$conflicts);
                    $err = "Package {$pChan}/{$pName} {$pVer} depends on PHP extensions: ".$conflicts;
                    if($forceMode) {
                        $this->doError($command, $err);
                    } else {
                        throw new \Exception($err);
                    }
                }

                $conflicts = $package->checkPhpVersion();
                if(true !== $conflicts) {
                    $err = "Package {$pChan}/{$pName} {$pVer}: ".$conflicts;
                    if($forceMode) {
                        $this->doError($command, $err);
                    } else {
                        throw new \Exception($err);
                    }
                }


                if(!$noFilesInstall) {
                    if($ftp) {
                        $packager->processInstallPackageFtp($package, $filename, $config, $ftpObj);
                    } else {
                        $packager->processInstallPackage($package, $filename, $config);
                    }
                }
                $cache->addPackage($package);
                $installedDeps = array();
                $installedDepsAssoc = array();
                $installedDepsAssoc[] = array('channel'=>$pChan, 'name'=>$pName, 'version'=>$pVer);
                $installedDeps[] = array($pChan, $pName, $pVer);


                $title = isset($options['title']) ? $options['title'] : "Package installed: ";
                $out = array($command => array('data'=>$installedDeps, 'assoc'=>$installedDepsAssoc,  'title'=>$title));

                if($ftp) {
                    $packager->writeToRemoteCache($cache, $ftpObj);
                    @unlink($config->getFilename());
                }

                $this->ui()->output($out);
                return $out[$command]['data'];
            }

            if(!$upgradeAllMode) {

                if(count($params) < 2) {
                    throw new \Exception("Argument should be: channelName packageName");
                }
                $channel = $params[0];
                $package = $params[1];
                $argVersionMax = isset($params[2]) ? $params[2]: false;
                $argVersionMin = false;

                if($cache->isChannelName($channel)) {
                    $uri = $cache->chanUrl($channel);
                } elseif($this->validator()->validateUrl($channel)) {
                    $uri = $channel;
                } elseif($channel) {
                    $uri = $config->protocol.'://'.$channel;
                } else {
                    throw new \Exception("'{$channel}' is not existant channel name / valid uri");
                }

                if($uri && !$cache->isChannel($uri)) {
                    $rest->setChannel($uri);
                    $data = $rest->getChannelInfo();
                    $data->uri = $uri;
                    $cache->addChannel($data->name, $uri);
                    $this->ui()->output("Successfully added channel: ".$uri);
                }
                $channelName = $cache->chanName($channel);
                //var_dump($channelName);
                $packagesToInstall = $packager->getDependenciesList( $channelName, $package, $cache, $config, $argVersionMax, $argVersionMin, $withDepsMode);
                $packagesToInstall = $packagesToInstall['result'];
                //var_dump($packagesToInstall);

            } else {
                if(empty($params[0])) {
                    $channels = $cache->getChannelNames();
                } else {
                    $channel = $params[0];
                    if(!$cache->isChannel($channel)) {
                        throw new \Exception("'{$channel}' is not existant channel name / valid uri");
                    }
                    $channels = $cache->chanName($channel);
                }
                $packagesToInstall = array();
                $neededToUpgrade = $packager->getUpgradesList($channels, $cache, $config);
                foreach($neededToUpgrade as $chan=>$packages) {
                    foreach($packages as $name=>$data) {
                        $versionTo = $data['to'];
                        $tmp = $packager->getDependenciesList( $chan, $name, $cache, $config, $versionTo, $versionTo, $withDepsMode);
                        if(count($tmp['result'])) {
                            $packagesToInstall = array_merge($packagesToInstall, $tmp['result']);
                        }
                    }
                }
            }

            /**
             * Make installation
             */
            $installedDeps = array();
            $installedDepsAssoc = array();
            $keys = array();

            foreach($packagesToInstall as $package) {
                try {
                    $pName = $package['name'];
                    $pChan = $package['channel'];
                    $pVer = $package['downloaded_version'];
                    $rest->setChannel($cache->chanUrl($pChan));

                    /**
                     * Upgrade mode
                     */
                    if($upgradeMode && $cache->hasPackage($pChan, $pName, $pVer, $pVer)) {
                        $this->ui()->output("Already installed: {$pChan}/{$pName} {$pVer}, skipping");
                        continue;
                    }

                    $conflicts = $cache->hasConflicts($pChan, $pName, $pVer);

                    if(false !== $conflicts) {
                        $conflicts = implode(", ",$conflicts);
                        if($forceMode) {
                            $this->doError($command, "Package {$pChan}/{$pName} {$pVer} conflicts with: ".$conflicts);
                        } else {
                            throw new \Exception("Package {$pChan}/{$pName} {$pVer} conflicts with: ".$conflicts);
                        }
                    }

                     
                    /**
                     * Modifications
                     */
                    if ($upgradeMode && !$ignoreModifiedMode) {
                        if($ftp) {
                            $modifications = $packager->getRemoteModifiedFiles($pChan, $pName, $cache, $config, $ftp);
                        } else {
                            $modifications = $packager->getLocalModifiedFiles($pChan, $pName, $cache, $config);
                        }
                        if (count($modifications) > 0) {
                            $this->ui()->output('Changed locally: ');
                            foreach ($modifications as $row) {
                                if(!$ftp) {
                                    $this->ui()->output($config->magento_root.DS.$row);
                                } else {
                                    $this->ui()->output($row);
                                }
                            }
                            /*$this->ui()->confirm('Do you want rewrite all files?');
                             continue;*/
                        }
                    }

                    $dir = $config->getChannelCacheDir($pChan);
                    @mkdir($dir, 0777, true);
                    $file = $dir.DIRECTORY_SEPARATOR.$pName."-".$pVer.".tgz";
                    if(!@file_exists($file)) {
                        $rest->downloadPackageFileOfRelease($pName, $pVer, $file);
                    }
                    $package = new \Magento\Connect\Package($file);



                    $conflicts = $package->checkPhpDependencies();
                    if(true !== $conflicts) {                       
                        $confilcts = implode(",",$conflicts);
                        $err = "Package {$pChan}/{$pName} {$pVer} depends on PHP extensions: ".$conflicts;
                        if($forceMode) {
                            $this->doError($command, $err);
                        } else {
                            throw new \Exception($err);
                        }
                    }

                    $conflicts = $package->checkPhpVersion();
                    if(true !== $conflicts) {
                        $err = "Package {$pChan}/{$pName} {$pVer}: ".$conflicts;
                        if($forceMode) {
                            $this->doError($command, $err);
                        } else {
                            throw new \Exception($err);
                        }
                    }

                    if(!$noFilesInstall) {
                        if($ftp) {
                            $packager->processInstallPackageFtp($package, $file, $config, $ftpObj);
                        } else {
                            $packager->processInstallPackage($package, $file, $config);
                        }
                    }
                    $cache->addPackage($package);

                    $installedDepsAssoc[] = array('channel'=>$pChan, 'name'=>$pName, 'version'=>$pVer);
                    $installedDeps[] = array($pChan, $pName, $pVer);

                } catch(\Exception $e) {
                    $this->doError($command, $e->getMessage());
                }
            }



            $title = isset($options['title']) ? $options['title'] : "Package installed: ";
            $out = array($command => array('data'=>$installedDeps, 'assoc'=>$installedDepsAssoc,  'title'=>$title));

            if($ftp) {
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            }

            $this->ui()->output($out);
            return $out[$command]['data'];

        } catch (\Exception $e) {
            if($ftp) {
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            }
            return $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Upgrade action callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doUpgrade($command, $options, $params)
    {
        $options['title'] = "Package upgraded: ";
        return $this->doInstall($command, $options, $params);
    }

    /**
     * Updgrade action callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doUpgradeAll($command, $options, $params)
    {
        $options['title'] = "Package upgraded: ";
        return $this->doInstall($command, $options, $params);
    }

    /**
     * Uninstall package callback
     * @param string $command
     * @param array $options
     * @param array $params
     * @return unknown_type
     */
    public function doUninstall($command, $options, $params)
    {
        $this->cleanupParams($params);
        //$this->splitPackageArgs($params);

        try {
            if(count($params) != 2) {
                throw new \Exception("Argument count should be = 2");
            }

            $channel = $params[0];
            $package = $params[1];
            $packager = $this->getPackager();
            $withDepsMode = !isset($options['nodeps']);
            $forceMode = isset($options['force']);

            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $cache = $this->getSconfig();
                $config = $this->config();
            }

            $chan = $cache->getChannel($channel);
            $channel = $cache->chanName($channel);
            if(!$cache->hasPackage($channel, $package)) {
                throw new \Exception("Package is not installed");
            }

            $deletedPackages = array();
            $list = $packager->getUninstallList($channel, $package, $cache, $config, $withDepsMode);
            foreach($list['list'] as $packageData) {
                try {
                    $reqd = $cache->requiredByOtherPackages($packageData['channel'], $packageData['name'], $list['list']);
                    if(count($reqd)) {
                        $errMessage = "{$packageData['channel']}/{$packageData['name']} {$packageData['version']} is required by: ";
                        $t = array();
                        foreach($reqd as $r) {
                            $t[] = $r['channel']."/".$r['name']. " ".$r['version'];
                        }
                        $errMessage .= implode(", ", $t);
                        if($forceMode) {
                            $this->ui()->output("Warning: ".$errMessage);
                        } else {
                            throw new \Exception($errMessage);
                        }
                    }

                    list($chan, $pack) = array($packageData['channel'], $packageData['name']);
                    if($ftp) {
                        $packager->processUninstallPackageFtp($chan, $pack, $cache, $config,  $ftp);
                    } else {
                        $packager->processUninstallPackage($chan, $pack, $cache, $config);
                    }
                    $cache->deletePackage($chan, $pack);
                    $deletedPackages[] = array($chan, $pack);

                } catch(\Exception $e) {
                    if($forceMode) {
                        $this->doError($command, $e->getMessage());
                    } else {
                        throw new \Exception($e->getMessage());
                    }
                }
            }
            if($ftp) {
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            }
            $out = array($command=>array('data'=>$deletedPackages, 'title'=>'Package deleted: '));
            $this->ui()->output($out);

        } catch (\Exception $e) {
            return $this->doError($command, $e->getMessage());
        }

    }

}

