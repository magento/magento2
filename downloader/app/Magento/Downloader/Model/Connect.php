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

namespace Magento\Downloader\Model;

include_once "Magento/Connect.php";

/**
 * Class for initialize Magento_Connect lib
 *
 * @category   Magento
 * @package    Magento_Connect
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Connect extends \Magento\Downloader\Model
{
    /**
     * Retrive object of \Magento\Downloader\Connect
     *
     * @return \Magento\Downloader\Connect
     */
    public function connect()
    {
        return \Magento\Downloader\Connect::getInstance();
    }

    /**
     * Install All Magento
     *
     * @param boolean $force
     */
    public function installAll($force=false, $chanName='')
    {
        $options = array('install_all'=>true);
        if ($force) {
            $this->connect()->cleanSconfig();
            $options['force'] = 1;
        }
        $packages = array(
            'Magento_All_Latest',
        );
        $connectConfig = $this->connect()->getConfig();
        $ftp = $connectConfig->remote_config;
        if (!empty($ftp)) {
            $options['ftp'] = $ftp;
        }
        $params = array();

        $uri = $this->controller()->channelConfig()->getRootChannelUri();

        $this->controller()->channelConfig()->setCommandOptions($this->controller()->session(), $options);

        $connectConfig->root_channel = $chanName;
        foreach ($packages as $package) {
            $params[] = $uri;
            $params[] = $package;
        }
        $this->connect()->runHtmlConsole(array('command'=>'install', 'options'=>$options, 'params'=>$params));
    }

    /**
     * Prepare to install package
     *
     * @param string $id
     * @return array
     */
    public function prepareToInstall($id)
    {
        $match = array();
        if (!$this->checkExtensionKey($id, $match)) {
            echo('Invalid package identifier provided: '.$id);
            exit;
        }

        $channel = $match[1];
        $package = $match[2];
        $version = (!empty($match[3]) ? trim($match[3],'/\-') : '');

        $connect = $this->connect();
        $sconfig = $connect->getSingleConfig();

        $options = array();
        $params = array($channel, $package, $version, $version);
        $this->controller()->channelConfig()->setCommandOptions($this->controller()->session(), $options);

        $connect->run('package-prepare', $options, $params);
        $output = $connect->getOutput();
        $errors = $connect->getFrontend()->getErrors();
        $package_error = array();
        foreach ($errors as $error){
            if (isset($error[1])){
                $package_error[] = $error[1];
            }
        }

        $packages = array();
        if (is_array($output) && isset($output['package-prepare'])){
            $packages = array_merge($output['package-prepare'], array('errors'=>array('error'=>$package_error)));
        } elseif (is_array($output) && !empty($package_error)) {
            $packages = array('errors'=>array('error'=>$package_error));
        }
        return $packages;
    }


    /**
     * Retrieve all installed packages
     *
     * @return array
     */
    public function getAllInstalledPackages()
    {
        $connect = $this->connect();
        $sconfig = $connect->getSingleConfig(true);
        $connect->run('list-installed');
        $output = $connect->getOutput();
        $packages = array();
        if (is_array($output) && isset($output['list-installed']['data'])){
            $packages = $output['list-installed']['data'];
        } else {

        }
        foreach ($packages as $channel=>$package) {
            foreach ($package as $name=>$data) {
                $summary = $sconfig->getPackageObject($channel, $name)->getSummary();
                $addition = array('summary'=>$summary, 'upgrade_versions'=>array(), 'upgrade_latest'=>'');
                $packages[$channel][$name] = array_merge($data, $addition);
            }
        }

        if (!empty($_GET['updates'])) {
            $options = array();
            $this->controller()->channelConfig()->setCommandOptions($this->controller()->session(), $options);
            $result = $connect->run('list-upgrades', $options);
            $output = $connect->getOutput();
            if (is_array($output)) {
                $channelData = $output;
                if (!empty($channelData['list-upgrades']['data']) && is_array($channelData['list-upgrades']['data'])) {
                    foreach ($channelData['list-upgrades']['data'] as $channel=>$package) {
                        foreach ($package as $name=>$data) {
                            if (!isset($packages[$channel][$name])) {
                                continue;
                            }
                            $packages[$channel][$name]['upgrade_latest'] = $data['to'].' ('.$data['from'].')';
                        }
                    }
                }
            }
        }

        $states = array('snapshot'=>0, 'devel'=>1, 'alpha'=>2, 'beta'=>3, 'stable'=>4);
        $preferredState = $states[$this->getPreferredState()];

        foreach ($packages as $channel=>&$package) {
            foreach ($package as $name=>&$data) {
                $actions = array();
                $systemPkg = $name==='Magento_Downloader';
                if (!empty($data['upgrade_latest'])) {
                    $status = 'upgrade-available';
                    $releases = array();
                    $connect->run('info', array(), array($channel, $name));
                    $output = $connect->getOutput();
                    if (!empty($output['info']['releases'])) {
                        foreach ($output['info']['releases'] as $release) {
                            $stability = $packages[$channel][$name]['stability'];
                            if ($states[$release['s']] < min($preferredState, $states[$stability])) {
                                continue;
                            }
                            if (version_compare($release['v'], $packages[$channel][$name]['version']) < 1) {
                                continue;
                            }
                            $releases[$release['v']] = $release['v'].' ('.$release['s'].')';
                        }
                    }

                    if ($releases) {
                        uksort($releases, 'version_compare');
                        foreach ($releases as $version => $release) {
                            $actions['upgrade|'.$version] = 'Upgrade to '.$release;
                        }
                    } else {
                        $a = explode(' ', $data['upgrade_latest'], 2);
                        $actions['upgrade|'.$a[0]] = 'Upgrade';
                    }
                    if (!$systemPkg) {
                        $actions['uninstall'] = 'Uninstall';
                    }
                } else {
                    $status = 'installed';
                    $actions['reinstall'] = 'Reinstall';
                    if (!$systemPkg) {
                        $actions['uninstall'] = 'Uninstall';
                    }
                }
                $packages[$channel][$name]['actions'] = $actions;
                $packages[$channel][$name]['status'] = $status;
            }
        }
        return $packages;
    }

    /**
     * Run packages action
     *
     * @param mixed $packages
     */
    public function applyPackagesActions($packages, $ignoreLocalModification='')
    {
        $actions = array();
        foreach ($packages as $package=>$action) {
            if ($action) {
                $a = explode('|', $package);
                $b = explode('|', $action);
                $package = $a[1];
                $channel = $a[0];
                $version = '';
                if ($b[0]=='upgrade') {
                    $version = $b[1];
                }
                $actions[$b[0]][] = array($channel, $package, $version, $version);
            }
        }
        if (empty($actions)) {
            $this->connect()->runHtmlConsole('No actions selected');
            exit;
        }

        $this->controller()->startInstall();

        $options = array();
        if (!empty($ignoreLocalModification)) {
            $options = array('ignorelocalmodification'=>1);
        }
        if(!$this->controller()->isWritable()||strlen($this->connect()->getConfig()->__get('remote_config'))>0){
            $options['ftp'] = $this->connect()->getConfig()->__get('remote_config');
        }

        $this->controller()->channelConfig()->setCommandOptions($this->controller()->session(), $options);

        foreach ($actions as $action=>$packages) {
            foreach ($packages as $package) {
                switch ($action) {
                    case 'install': case 'uninstall': case 'upgrade':
                        $this->connect()->runHtmlConsole(array(
                            'command'=>$action,
                            'options'=>$options,
                            'params'=>$package
                        ));
                        break;

                    case 'reinstall':
                        $package_info = $this->connect()->getSingleConfig()->getPackage($package[0], $package[1]);
                        if (isset($package_info['version'])) {
                            $package[2] = $package_info['version'];
                            $package[3] = $package_info['version'];
                        }
                        $this->connect()->runHtmlConsole(array(
                            'command'=>'install',
                            'options'=>array_merge($options, array('force'=>1, 'nodeps'=>1)),
                            'params'=>$package
                        ));
                        break;
                }
            }
        }

        $this->controller()->endInstall();
    }


    public function installUploadedPackage($file)
    {
        $this->controller()->startInstall();

        $options = array();
        if(!$this->controller()->isWritable()||strlen($this->connect()->getConfig()->__get('remote_config'))>0){
            $options['ftp'] = $this->connect()->getConfig()->__get('remote_config');
        }
        $this->connect()->runHtmlConsole(array(
            'command'=>'install-file',
            'options'=>$options,
            'params'=>array($file),
        ));
        $this->controller()->endInstall();
    }

    /**
     * Install package by id
     *
     * @param string $id
     * @param boolean $force
     */
    public function installPackage($id, $force=false)
    {
        $match = array();
        if (!$this->checkExtensionKey($id, $match)) {
            $this->connect()->runHtmlConsole('Invalid package identifier provided: '.$id);
            exit;
        }

        $channel = $match[1];
        $package = $match[2];//.(!empty($match[3]) ? $match[3] : '');
        $version = (!empty($match[3]) ? trim($match[3],'/\-') : '');

        $this->controller()->startInstall();

        $options = array();
        if ($force) {
            $options['force'] = 1;
        }
        if(!$this->controller()->isWritable()||strlen($this->connect()->getConfig()->__get('remote_config'))>0){
            $options['ftp'] = $this->connect()->getConfig()->__get('remote_config');
        }

        $this->controller()->channelConfig()->setCommandOptions($this->controller()->session(), $options);

        $this->connect()->runHtmlConsole(array(
            'command'=>'install',
            'options'=>$options,
            'params'=>array(0=>$channel, 1=>$package, 2=>$version),
        ));

        $this->controller()->endInstall();
    }

    /**
     * Retrieve stability choosen client
     *
     * @return string alpha, beta, ...
     */
    public function getPreferredState()
    {
        if (is_null($this->get('preferred_state'))) {
            $connectConfig = $this->connect()->getConfig();
            $this->set('preferred_state', $connectConfig->__get('preferred_state'));
        }
        return $this->get('preferred_state');
    }

    /**
     * Retrieve protocol choosen client
     *
     * @return string http, ftp
     */
    public function getProtocol()
    {
        if (is_null($this->get('protocol'))) {
            $connectConfig = $this->connect()->getConfig();
            $this->set('protocol', $connectConfig->__get('protocol'));
        }
        return $this->get('protocol');
    }

    /**
     * Validate settings post data.
     *
     * @param array $p
     */
    public function validateConfigPost($p)
    {
        $errors = array();
        $configTestFile = 'connect.cfgt';
        $configObj = $this->connect()->getConfig();
        if ('ftp' == $p['deployment_type'] || '1' == $p['inst_protocol']) {
            /*check ftp*/

            $confFile = $configObj->downloader_path.DIRECTORY_SEPARATOR.$configTestFile;
            try {
                $ftpObj = new \Magento\Connect\Ftp();
                $ftpObj->connect($p['ftp']);
                $tempFile = tempnam(sys_get_temp_dir(),'config');
                $serial = md5('config test file');
                $f = @fopen($tempFile, "w+");
                @fwrite($f, $serial);
                @fclose($f);
                $ret=$ftpObj->upload($confFile, $tempFile);

                //read file
                if (!$errors && is_file($configTestFile)) {
                    $size = filesize($configTestFile);
                    if(!$size) {
                        $errors[]='Unable to read saved settings. Please check Installation Path of FTP Connection.';
                    }

                    if (!$errors) {
                        $f = @fopen($configTestFile, "r");
                        @fseek($f, 0, SEEK_SET);

                        $contents = @fread($f, strlen($serial));
                        if ($serial != $contents) {
                            $errors[]='Wrong Installation Path of FTP Connection.';
                        }
                        fclose($f);
                    }
                } else {
                    $errors[] = 'Unable to read saved settings. Please check Installation Path of FTP Connection.';
                }
                $ftpObj->delete($confFile);
                $ftpObj->close();
            } catch (\Exception $e) {
                $errors[] = 'Deployment FTP Error. ' . $e->getMessage();
            }
        } else {
            $p['ftp'] = '';
        }

        if ('1' == $p['use_custom_permissions_mode']) {
            /*check permissions*/
            if (octdec(intval($p['mkdir_mode'])) < 73 || octdec(intval($p['mkdir_mode'])) > 511) {
                $errors[]='Folders permissions not valid. ';
            }
            if (octdec(intval($p['chmod_file_mode'])) < 73 || octdec(intval($p['chmod_file_mode'])) > 511) {
                $errors[]='Files permissions not valid. ';
            }
        }
        //$this->controller()->session()->addMessage('success', 'Settings has been successfully saved');
        return $errors;
    }
    /**
     * Save settings.
     *
     * @param array $p
     */
    public function saveConfigPost($p)
    {
        $configObj = $this->connect()->getConfig();
        if ('ftp' == $p['deployment_type'] || '1' == $p['inst_protocol']){
            $this->set('ftp',$p['ftp']);
        } else {
            $p['ftp'] = '';
        }
        $configObj->remote_config = $p['ftp'];
        $configObj->preferred_state = $p['preferred_state'];
        $configObj->protocol = $p['protocol'];
        $configObj->use_custom_permissions_mode = $p['use_custom_permissions_mode'];
        if ('1' == $p['use_custom_permissions_mode']) {
            $configObj->global_dir_mode = octdec(intval($p['mkdir_mode']));
            $configObj->global_file_mode = octdec(intval($p['chmod_file_mode']));
        }
        if ($configObj->save()) {
            $this->controller()->session()->addMessage('success', 'Settings has been successfully saved');
        } else {
            $this->controller()->session()->addMessage('error', 'Settings cannot be saved');
        }
        return $this;
    }

    /**
     * Check Extension Key
     *
     * @param string $id
     * @param array $match
     * @return int
     */
    public function checkExtensionKey($id, &$match)
    {
        return preg_match('#^([^ ]+)\/([^-]+)(-.+)?$#', $id, $match);
    }
}
