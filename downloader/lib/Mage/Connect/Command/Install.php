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
final class Mage_Connect_Command_Install extends Mage_Connect_Command
{
    /**
     * Install action callback
     *
     * @throws Exception
     * @param string $command
     * @param array $options
     * @param array $params
     * @param array $objects
     * @return array|null
     */
    public function doInstall($command, $options, $params, $objects = array())
    {
        $this->cleanupParams($params);

        $installFileMode = $command === 'install-file';

        /** @var $ftpObj Mage_Connect_Ftp */
        $ftpObj=null;
        $ftp = empty($options['ftp']) ? false : $options['ftp'];
        /** @var $packager Mage_Connect_Packager */
        $packager = $this->getPackager();
        /** @var $cache Mage_Connect_Singleconfig */
        /** @var $config Mage_Connect_Config */
        if ($ftp) {
            list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
        } else {
            $cache = $this->getSconfig();
            $config = $this->config();
        }

        try {
            $forceMode = isset($options['force']);
            $upgradeAllMode = $command == 'upgrade-all';
            $upgradeMode = $command == 'upgrade' || $command == 'upgrade-all';
            $noFilesInstall = isset($options['nofiles']);
            $withDepsMode = !isset($options['nodeps']);
            $ignoreModifiedMode = true || !isset($options['ignorelocalmodification']);
            $clearInstallMode = $command == 'install' && !$forceMode;
            $installAll = isset($options['install_all']);
            $channelAuth = isset($options['auth'])?$options['auth']:array();

            $rest = $this->rest();
            if (empty($config->magento_root)) {
                $config->magento_root=dirname(dirname($_SERVER['SCRIPT_FILENAME']));
            }
            chdir($config->magento_root);
            $dirCache = DIRECTORY_SEPARATOR . $config->downloader_path . DIRECTORY_SEPARATOR
                . Mage_Connect_Config::DEFAULT_CACHE_PATH;
            $dirTmp = DIRECTORY_SEPARATOR . Mage_Connect_Package_Reader::PATH_TO_TEMPORARY_DIRECTORY;
            $dirMedia = DIRECTORY_SEPARATOR . 'media';
            $isWritable = true;
            if ($ftp) {
                $cwd=$ftpObj->getcwd();
                $ftpObj->mkdirRecursive($cwd . $dirCache,0777);
                $ftpObj->chdir($cwd);
                $ftpObj->mkdirRecursive($cwd . $dirTmp,0777);
                $ftpObj->chdir($cwd);
                $ftpObj->mkdirRecursive($cwd . $dirMedia,0777);
                $ftpObj->chdir($cwd);
                $err = "Please check for sufficient ftp write file permissions.";
            } else {
                @mkdir($config->magento_root . $dirCache,0777,true);
                @mkdir($config->magento_root . $dirTmp,0777,true);
                @mkdir($config->magento_root . $dirMedia,0777,true);
                $isWritable = is_writable($config->magento_root)
                              && is_writable($config->magento_root . DIRECTORY_SEPARATOR . $config->downloader_path)
                              && is_writable($config->magento_root . $dirCache)
                              && is_writable($config->magento_root . $dirTmp)
                              && is_writable($config->magento_root . $dirMedia);
                $err = "Please check for sufficient write file permissions.";
            }
            $isWritable = $isWritable && is_writable($config->magento_root . $dirMedia)
                          && is_writable($config->magento_root . $dirCache)
                          && is_writable($config->magento_root . $dirTmp);
            if (!$isWritable) {
                $this->doError($command, $err);
                throw new Exception(
                    'Your Magento folder does not have sufficient write permissions, which downloader requires.'
                );
            }
            if (!empty($channelAuth)) {
                $rest->getLoader()->setCredentials($channelAuth['username'], $channelAuth['password']);
            }

            if ($installFileMode) {
                if (count($params) < 1) {
                    throw new Exception("Argument should be: filename");
                }
                $filename = $params[0];
                if (!@file_exists($filename)) {
                    throw new Exception("File '{$filename}' not found");
                }
                if (!@is_readable($filename)) {
                    throw new Exception("File '{$filename}' is not readable");
                }

                $package = new Mage_Connect_Package($filename);
                $package->setConfig($config);
                $package->validate();
                $errors = $package->getErrors();
                if (count($errors)) {
                    throw new Exception("Package file is invalid\n" . implode("\n", $errors));
                }

                $pChan = $package->getChannel();
                $pName = $package->getName();
                $pVer = $package->getVersion();

                if (!($cache->isChannelName($pChan) || $cache->isChannelAlias($pChan))) {
                    throw new Exception("The '{$pChan}' channel is not installed. Please use the MAGE shell "
                        . "script to install the '{$pChan}' channel.");
                }

                $conflicts = $cache->hasConflicts($pChan, $pName, $pVer);

                if (false !== $conflicts) {
                    $conflicts = implode(", ",$conflicts);
                    if ($forceMode) {
                        $this->doError($command, "Package {$pChan}/{$pName} {$pVer} conflicts with: " . $conflicts);
                    } else {
                        throw new Exception("Package {$pChan}/{$pName} {$pVer} conflicts with: " . $conflicts);
                    }
                }

                $conflicts = $package->checkPhpDependencies();
                if (true !== $conflicts) {
                    $conflicts = implode(",",$conflicts);
                    $err = "Package {$pChan}/{$pName} {$pVer} depends on PHP extensions: " . $conflicts;
                    if ($forceMode) {
                        $this->doError($command, $err);
                    } else {
                        throw new Exception($err);
                    }
                }

                $conflicts = $package->checkPhpVersion();
                if (true !== $conflicts) {
                    $err = "Package {$pChan}/{$pName} {$pVer}: " . $conflicts;
                    if ($forceMode) {
                        $this->doError($command, $err);
                    } else {
                        throw new Exception($err);
                    }
                }

                if (!$noFilesInstall) {
                    if ($ftp) {
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

                if ($ftp) {
                    $packager->writeToRemoteCache($cache, $ftpObj);
                    @unlink($config->getFilename());
                }

                $this->ui()->output($out);
                return $out[$command]['data'];
            }

            if (!$upgradeAllMode) {
                if (count($params) < 2) {
                    throw new Exception("Argument should be: channelName packageName");
                }
                $channel = $params[0];
                $package = $params[1];
                $argVersionMax = isset($params[2]) ? $params[2]: false;
                $argVersionMin = isset($params[3]) ? $params[3]: false;

                $cache->checkChannel($channel, $config, $rest);
                $channelName = $cache->chanName($channel);
                $this->ui()->output("Checking dependencies of packages");
                $packagesToInstall = $packager->getDependenciesList($channelName, $package, $cache, $config,
                    $argVersionMax, $argVersionMin, $withDepsMode, false, $rest
                );
                /*
                 * process 'failed' results
                 */
                if (count($packagesToInstall['failed'])) {
                    $showError=!count($packagesToInstall['result']);
                    foreach ($packagesToInstall['failed'] as $failed) {
                        $msg="Package {$failed['channel']}/{$failed['name']} failed: " . $failed['reason'];
                        if ($showError) {
                            $this->doError($command, $msg);
                        } else {
                            $this->ui()->output($msg);
                        }
                    }
                }
                $packagesToInstall = $packagesToInstall['result'];
            } else {
                if (empty($params[0])) {
                    $channels = $cache->getChannelNames();
                } else {
                    $channel = $params[0];
                    if (!$cache->isChannel($channel)) {
                        throw new Exception("'{$channel}' is not existant channel name / valid uri");
                    }
                    $channels = $cache->chanName($channel);
                }
                $packagesToInstall = array();
                $neededToUpgrade = $packager->getUpgradesList($channels, $cache, $config, $rest);
                foreach ($neededToUpgrade as $chan=>$packages) {
                    foreach ($packages as $name=>$data) {
                        $versionTo = $data['to'];
                        $tmp = $packager->getDependenciesList($chan, $name, $cache, $config, $versionTo, $versionTo,
                            $withDepsMode, false, $rest
                        );
                        if (count($tmp['result'])) {
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

            foreach ($packagesToInstall as $package) {
                try {
                    $pName = $package['name'];
                    $pChan = $package['channel'];
                    $pVer = $package['downloaded_version'];
                    $pInstallState = $package['install_state'];
                    $rest->setChannel($cache->chanUrl($pChan));

                    /**
                     * Skip existing packages
                     */
                    if ($upgradeMode && $cache->hasPackage($pChan, $pName, $pVer, $pVer)
                        || ('already_installed' == $pInstallState && !$forceMode)
                    ) {
                        $this->ui()->output("Already installed: {$pChan}/{$pName} {$pVer}, skipping");
                        continue;
                    }

                    if ('incompartible' == $pInstallState) {
                        $this->ui()->output(
                            "Package incompartible with installed Magento: {$pChan}/{$pName} {$pVer}, skipping"
                        );
                        continue;
                    }

                    $conflicts = $cache->hasConflicts($pChan, $pName, $pVer);

                    if (false !== $conflicts) {
                        $conflicts = implode(", ",$conflicts);
                        if ($forceMode) {
                            $this->doError($command, "Package {$pChan}/{$pName} {$pVer} conflicts with: " . $conflicts);
                        } else {
                            throw new Exception("Package {$pChan}/{$pName} {$pVer} conflicts with: " . $conflicts);
                        }
                    }

                    /**
                     * Modifications
                     */
                    if (($upgradeMode || ($pInstallState == 'upgrade')) && !$ignoreModifiedMode) {
                        if ($ftp) {
                            $modifications = $packager->getRemoteModifiedFiles($pChan, $pName, $cache, $config, $ftp);
                        } else {
                            $modifications = $packager->getLocalModifiedFiles($pChan, $pName, $cache, $config);
                        }
                        if (count($modifications) > 0) {
                            $this->ui()->output('Changed locally: ');
                            foreach ($modifications as $row) {
                                if (!$ftp) {
                                    $this->ui()->output($config->magento_root . DS . $row);
                                } else {
                                    $this->ui()->output($row);
                                }
                            }
                        }
                    }

                    if ($ftp) {
                        $cwd=$ftpObj->getcwd();
                        $dir=$cwd . DIRECTORY_SEPARATOR .$config->downloader_path . DIRECTORY_SEPARATOR
                             . Mage_Connect_Config::DEFAULT_CACHE_PATH . DIRECTORY_SEPARATOR . trim( $pChan, "\\/");
                        $ftpObj->mkdirRecursive($dir,0777);
                        $ftpObj->chdir($cwd);
                    } else {
                        $dir = $config->getChannelCacheDir($pChan);
                        @mkdir($dir, 0777, true);
                    }
                    $dir = $config->getChannelCacheDir($pChan);
                    $packageFileName = $pName . "-" . $pVer . ".tgz";
                    $file = $dir . DIRECTORY_SEPARATOR . $packageFileName;
                    if (!@file_exists($file)) {
                        $this->ui()->output("Starting to download $packageFileName ...");
                        $rest->downloadPackageFileOfRelease($pName, $pVer, $file);
                        $this->ui()->output(sprintf("...done: %s bytes", number_format(filesize($file))));
                    }

                    /**
                     * Remove old version package before install new
                     */
                    if ($cache->hasPackage($pChan, $pName)) {
                        if ($ftp) {
                            $packager->processUninstallPackageFtp($pChan, $pName, $cache, $ftpObj);
                        } else {
                            $packager->processUninstallPackage($pChan, $pName, $cache, $config);
                        }
                        $cache->deletePackage($pChan, $pName);
                    }

                    $package = new Mage_Connect_Package($file);
                    if ($clearInstallMode && $pInstallState != 'upgrade' && !$installAll) {
                        $this->validator()->validateContents($package->getContents(), $config);
                        $errors = $this->validator()->getErrors();
                        if (count($errors)) {
                            throw new Exception("Package '{$pName}' is invalid\n" . implode("\n", $errors));
                        }
                    }

                    $conflicts = $package->checkPhpDependencies();
                    if (true !== $conflicts) {
                        $conflicts = implode(",",$conflicts);
                        $err = "Package {$pChan}/{$pName} {$pVer} depends on PHP extensions: " . $conflicts;
                        if ($forceMode) {
                            $this->doError($command, $err);
                        } else {
                            throw new Exception($err);
                        }
                    }

                    $conflicts = $package->checkPhpVersion();
                    if (true !== $conflicts) {
                        $err = "Package {$pChan}/{$pName} {$pVer}: " . $conflicts;
                        if ($forceMode) {
                            $this->doError($command, $err);
                        } else {
                            throw new Exception($err);
                        }
                    }

                    if (!$noFilesInstall) {
                        $this->ui()->output("Installing package {$pChan}/{$pName} {$pVer}");
                        if ($ftp) {
                            $packager->processInstallPackageFtp($package, $file, $config, $ftpObj);
                        } else {
                            $packager->processInstallPackage($package, $file, $config);
                        }
                        $this->ui()->output("Package {$pChan}/{$pName} {$pVer} installed successfully");
                    }
                    $cache->addPackage($package);

                    $installedDepsAssoc[] = array('channel'=>$pChan, 'name'=>$pName, 'version'=>$pVer);
                    $installedDeps[] = array($pChan, $pName, $pVer);

                } catch(Exception $e) {
                    $this->doError($command, $e->getMessage());
                }
            }

            $title = isset($options['title']) ? $options['title'] : "Package installed: ";
            $out = array($command => array('data'=>$installedDeps, 'assoc'=>$installedDepsAssoc,  'title'=>$title));

            if ($ftp) {
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            }

            $this->ui()->output($out);
            return $out[$command]['data'];
        } catch (Exception $e) {
            if ($ftp) {
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            }
            return $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Upgrade action callback
     *
     * @param string $command
     * @param array $options
     * @param array $params
     * @return array|null
     */
    public function doUpgrade($command, $options, $params)
    {
        $options['title'] = "Package upgraded: ";
        return $this->doInstall($command, $options, $params);
    }

    /**
     * Updgrade action callback
     *
     * @param string $command
     * @param array $options
     * @param array $params
     * @return array|null
     */
    public function doUpgradeAll($command, $options, $params)
    {
        $options['title'] = "Package upgraded: ";
        return $this->doInstall($command, $options, $params);
    }

    /**
     * Uninstall package callback
     *
     * @param string $command
     * @param array $options
     * @param array $params
     * @return array|null
     */
    public function doUninstall($command, $options, $params)
    {
        $this->cleanupParams($params);

        try {
            if (count($params) != 2) {
                throw new Exception("Argument count should be = 2");
            }

            $channel = $params[0];
            $package = $params[1];
            /** @var $packager Mage_Connect_Packager */
            $packager = $this->getPackager();
            $withDepsMode = !isset($options['nodeps'])? false : (boolean)$options['nodeps'];
            $forceMode = isset($options['force']);

            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            /** @var $cache Mage_Connect_Singleconfig */
            /** @var $config Mage_Connect_Config */
            /** @var $ftpObj Mage_Connect_Ftp */
            if ($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            } else {
                $cache = $this->getSconfig();
                $config = $this->config();
            }

            $channel = $cache->chanName($channel);
            if (!$cache->hasPackage($channel, $package)) {
                throw new Exception("Package is not installed");
            }

            $deletedPackages = array();
            $list = $packager->getUninstallList($channel, $package, $cache, $config, $withDepsMode);
            foreach ($list['list'] as $packageData) {
                try {
                    $reqd = $cache->requiredByOtherPackages(
                        $packageData['channel'],
                        $packageData['name'],
                        $list['list']
                    );
                    if (count($reqd)) {
                        $errMessage = "{$packageData['channel']}/{$packageData['name']} "
                            . "{$packageData['version']} is required by: ";
                        $t = array();
                        foreach ($reqd as $r) {
                            $t[] = $r['channel'] . "/" . $r['name'] . " " . $r['version'];
                        }
                        $errMessage .= implode(", ", $t);
                        if ($forceMode) {
                            $this->ui()->output("Warning: " . $errMessage);
                        } else {
                            throw new Exception($errMessage);
                        }
                    }
                } catch(Exception $e) {
                    if ($forceMode) {
                        $this->doError($command, $e->getMessage());
                    } else {
                        throw new Exception($e->getMessage());
                    }
                }
            }
            foreach ($list['list'] as $packageData) {
                try {
                    list($chan, $pack) = array($packageData['channel'], $packageData['name']);
                    $packageName = $packageData['channel'] . "/" . $packageData['name'];
                    $this->ui()->output("Starting to uninstall $packageName ");
                    if ($ftp) {
                        $packager->processUninstallPackageFtp($chan, $pack, $cache, $ftpObj);
                    } else {
                        $packager->processUninstallPackage($chan, $pack, $cache, $config);
                    }
                    $cache->deletePackage($chan, $pack);
                    $deletedPackages[] = array($chan, $pack);
                    $this->ui()->output("Package {$packageName} uninstalled");
                } catch(Exception $e) {
                    if ($forceMode) {
                        $this->doError($command, $e->getMessage());
                    } else {
                        throw new Exception($e->getMessage());
                    }
                }
            }
            if ($ftp) {
                $packager->writeToRemoteCache($cache, $ftpObj);
                @unlink($config->getFilename());
            }
            $out = array($command=>array('data'=>$deletedPackages, 'title'=>'Package deleted: '));
            $this->ui()->output($out);
            return $out[$command]['data'];
        } catch (Exception $e) {
            return $this->doError($command, $e->getMessage());
        }
    }
}
