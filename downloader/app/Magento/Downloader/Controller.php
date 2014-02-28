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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class Controller
 *
 * @category   Magento
 * @package    Magento_Connect
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloader;

final class Controller
{
    /**
     * Request key of action
     */
    const ACTION_KEY = 'A';

    /**
     * Instance of class
     *
     * @var \Magento\Downloader\Controller
     */
    private static $_instance;

    /**
     * Current action name
     *
     * @var string
     */
    private $_action;

    /**
     * Controller is dispathed flag
     *
     * @var bool
     */
    private $_isDispatched = false;

    /**
     * Redirect to URL
     *
     * @var string
     */
    private $_redirectUrl;

    /**
     * Downloader dir path
     *
     * @var string
     */
    private $_rootDir;

    /**
     * Magento root dir path
     *
     * @var string
     */
    private $_mageDir;

    /**
     * View instance
     *
     * @var \Magento\Downloader\View
     */
    private $_view;

    /**
     * Connect config instance
     *
     * @var \Magento\Connect\Config
     */
    private $_config;

    /**
     * Config instance
     *
     * @var \Magento\Downloader\Model\Config
     */
    private $_localConfig;

    /**
     * Session instance
     *
     * @var \Magento\Downloader\Model\Session
     */
    private $_session;

    /**
     * Root dir is writable flag
     *
     * @var bool
     */
    private $_writable;

    /**
     * Use maintenance flag
     *
     * @var bool
     */
    protected $_maintenance;

    /**
     * Maintenance file path
     *
     * @var string
     */
    protected $_maintenanceFile;

    /**
     * Register array for singletons
     *
     * @var array
     */
    protected $_singletons = array();

    //////////////////////////// ACTIONS

    /**
     * Get ftp string from post data
     *
     * @param array $post post data
     * @return string FTP Url
     */
    private function getFtpPost($post){
        if (empty($post['ftp_host'])) {
            $_POST['ftp'] = '';
            return '';
        }
        $ftp = 'ftp://';
        $post['ftp_proto'] = 'ftp://';

        if (!empty($post['ftp_path']) && strlen(trim($post['ftp_path'], '\\/')) > 0) {
            $post['ftp_path'] = '/' . trim($post['ftp_path'], '\\/') . '/';
        } else {
            $post['ftp_path'] = '/';
        }

        $start = stripos($post['ftp_host'],'ftp://');
        if ($start !== false){
            $post['ftp_proto'] = 'ftp://';
            $post['ftp_host']  = substr($post['ftp_host'], $start + 6 - 1);
        }
        $start = stripos($post['ftp_host'],'ftps://');
        if ($start !== false) {
            $post['ftp_proto'] = 'ftps://';
            $post['ftp_host']  = substr($post['ftp_host'], $start + 7 - 1);
        }

        $post['ftp_host'] = trim($post['ftp_host'], '\\/');

        if (!empty($post['ftp_login']) && !empty($post['ftp_password'])){
            $ftp = sprintf("%s%s:%s@%s%s",
                    $post['ftp_proto'],
                    $post['ftp_login'],
                    $post['ftp_password'],
                    $post['ftp_host'],
                    $post['ftp_path']
            );
        } elseif (!empty($post['ftp_login'])) {
            $ftp = sprintf(
                "%s%s@%s%s",
                $post['ftp_proto'],
                $post['ftp_login'],
                $post['ftp_host'],
                $post['ftp_path']
            );
        } else {
            $ftp = $post['ftp_proto'] . $post['ftp_host'] . $post['ftp_path'];
        }

        $_POST['ftp'] = $ftp;
        return $ftp;
    }

    /**
     * NoRoute
     */
    public function norouteAction()
    {
        header("HTTP/1.0 404 Invalid Action");
        echo $this->view()->template('noroute.phtml');
    }

    /**
     * Login
     */
    public function loginAction()
    {
        $this->view()->set('username', !empty($_GET['username']) ? $_GET['username'] : '');
        echo $this->view()->template('login.phtml');
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        $this->session()->logout();
        $this->redirect($this->url());
    }

    /**
     * Index
     */
    public function indexAction()
    {
        $config = $this->config();
        if (!$this->isInstalled()) {
            $this->view()->set('mage_url', dirname(dirname($_SERVER['SCRIPT_NAME'])));
            $this->view()->set(
                'use_custom_permissions_mode',
                $config->__get('use_custom_permissions_mode')
                    ? $config->__get('use_custom_permissions_mode')
                    : '0'
            );
            $this->view()->set('mkdir_mode', decoct($config->__get('global_dir_mode')));
            $this->view()->set('chmod_file_mode', decoct($config->__get('global_file_mode')));
            $this->view()->set('protocol', $config->__get('protocol'));
            $this->channelConfig()->setInstallView($config,$this->view());

            echo $this->view()->template('install/download.phtml');
        } elseif (!$config->sync_pear) {
            $this->model('connect', true)->connect()->run('sync');
            $this->forward('connectPackages');
        } else {
            $this->forward('connectPackages');
        }
    }

    /**
     * Empty Action
     */
    public function emptyAction()
    {
        $this->model('connect', true)
            ->connect()
            ->runHtmlConsole('Please wait, preparing for updates...');
    }

    /**
     * Install all magento
     */
    public function connectInstallAllAction()
    {
        $p = &$_POST;
        $this->getFtpPost($p);
        $errors = $this->model('connect', true)->validateConfigPost($p);
        /* todo show errors */
        if ($errors) {
            $message = "CONNECT ERROR: ";
            foreach ($errors as $err) {
                $message .= $err . "\n";
            }
            $this->model('connect', true)->connect()->runHtmlConsole($message);
            $this->model('connect', true)->connect()->showConnectErrors($errors);
            return;
        }

        if( 1 == $p['inst_protocol']){
            $this->model('connect', true)->connect()->setRemoteConfig($this->getFtpPost($p));
        }

        $this->channelConfig()->setPostData($this->config(),$p);

        $chan = $this->config()->__get('root_channel');
        $this->model('connect', true)->saveConfigPost($_POST);
        $this->channelConfig()->setSettingsSession($_POST, $this->session());
        $this->model('connect', true)->installAll(!empty($_GET['force']), $chan);
        $p = null;
    }

    /**
     * Connect packages
     */
    public function connectPackagesAction()
    {
        $connect = $this->model('connect', true);

        if (isset($_GET['loggedin'])) {
            $connect->connect()->run('sync');
        }

        $this->view()->set('connect', $connect);
        $this->view()->set('channel_config', $this->channelConfig());
        $remoteConfig = $this->config()->remote_config;
        if (!$this->isWritable() && empty($remoteConfig)) {
            $this->view()->set('writable_warning', true);
        }

        echo $this->view()->template('connect/packages.phtml');
    }

    /**
     * Connect packages POST
     */
    public function connectPackagesPostAction()
    {
        $actions = isset($_POST['actions']) ? $_POST['actions'] : array();
        if (isset($_POST['ignore_local_modification'])) {
            $ignoreLocalModification = $_POST['ignore_local_modification'];
        } else {
            $ignoreLocalModification = '';
        }
        $this->model('connect', true)->applyPackagesActions($actions, $ignoreLocalModification);
    }

    /**
     * Prepare package to install, get dependency info.
     */
    public function connectPreparePackagePostAction()
    {
        if (!$_POST) {
            echo "INVALID POST DATA";
            return;
        }
        $prepareResult = $this->model('connect', true)->prepareToInstall($_POST['install_package_id']);

        $packages   = isset($prepareResult['data']) ? $prepareResult['data'] : array();
        $errors     = isset($prepareResult['errors']) ? $prepareResult['errors'] : array();

        $this->view()->set('packages', $packages);
        $this->view()->set('errors', $errors);
        $this->view()->set('package_id', $_POST['install_package_id']);

        echo $this->view()->template('connect/packages_prepare.phtml');
    }

    /**
     * Install package
     */
    public function connectInstallPackagePostAction()
    {
        if (!$_POST) {
            echo "INVALID POST DATA";
            return;
        }
        $this->model('connect', true)->installPackage($_POST['install_package_id']);
    }

    /**
     * Install uploaded package
     */
    public function connectInstallPackageUploadAction()
    {
        if (!$_FILES) {
            echo "No file was uploaded";
            return;
        }

        if(empty($_FILES['file'])) {
            echo "No file was uploaded";
            return;
        }

        $info =& $_FILES['file'];

        if(0 !== intval($info['error'])) {
            echo "File upload problem";
            return;
        }

        $target = $this->_mageDir . '/var/' . uniqid() . $info['name'];
        $res = move_uploaded_file($info['tmp_name'], $target);
        if(false === $res) {
            echo "Error moving uploaded file";
            return;
        }

        $this->model('connect', true)->installUploadedPackage($target);
        @unlink($target);
    }

    /**
     * Clean cache on ajax request
     */
    public function cleanCacheAction()
    {
        $result = $this->cleanCache();
        echo json_encode($result);
    }

    /**
     * Settings
     */
    public function settingsAction()
    {
        $config = $this->config();
        $this->view()->set('preferred_state', $config->__get('preferred_state'));
        $this->view()->set('protocol', $config->__get('protocol'));

        $this->view()->set('use_custom_permissions_mode', $config->__get('use_custom_permissions_mode'));
        $this->view()->set('mkdir_mode', decoct($config->__get('global_dir_mode')));
        $this->view()->set('chmod_file_mode', decoct($config->__get('global_file_mode')));

        $this->channelConfig()->setSettingsView($this->session(), $this->view());

        $fs_disabled =! $this->isWritable();
        $ftpParams = $config->__get('remote_config') ? parse_url($config->__get('remote_config')) : '';

        $this->view()->set('fs_disabled', $fs_disabled);
        $this->view()->set('deployment_type', ($fs_disabled || !empty($ftpParams) ? 'ftp' : 'fs'));

        if (!empty($ftpParams)) {
            $this->view()->set('ftp_host', sprintf("%s://%s", $ftpParams['scheme'], $ftpParams['host']));
            $this->view()->set('ftp_login', $ftpParams['user']);
            $this->view()->set('ftp_password', $ftpParams['pass']);
            $this->view()->set('ftp_path', $ftpParams['path']);
        }
        echo $this->view()->template('settings.phtml');
    }

    /**
     * Settings post
     */
    public function settingsPostAction()
    {
        if ($_POST) {
            $ftp = $this->getFtpPost($_POST);

            /* clear startup messages */
            $this->config();
            $this->session()->getMessages();

            $errors = $this->model('connect', true)->validateConfigPost($_POST);
            if ($errors) {
                foreach ($errors as $err) {
                    $this->session()->addMessage('error', $err);
                }
                $this->redirect($this->url('settings'));
                return;
            }
            try {
                if ('ftp' == $_POST['deployment_type'] && !empty($_POST['ftp_host'])) {
                    $this->model('connect', true)->connect()->setRemoteConfig($ftp);
                } else {
                    $this->model('connect', true)->connect()->setRemoteConfig('');
                    $_POST['ftp'] = '';
                }
                $this->channelConfig()->setPostData($this->config(), $_POST);
                $this->model('connect', true)->saveConfigPost($_POST);
                $this->channelConfig()->setSettingsSession($_POST, $this->session());
                $this->model('connect', true)->connect()->run('sync');
            } catch (\Exception $e) {
                $this->session()->addMessage('error', "Unable to save settings: " . $e->getMessage());
            }
        }
        $this->redirect($this->url('settings'));
    }

    //////////////////////////// ABSTRACT

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_rootDir = dirname(__DIR__);
        $this->_mageDir = dirname($this->_rootDir);
    }

    /**
     * Run
     */
    public static function run()
    {
        try {
            self::singleton()->dispatch();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Initialize object of class
     *
     * @return \Magento\Downloader\Controller
     */
    public static function singleton()
    {
        if (!self::$_instance) {
            self::$_instance = new self;

            if (self::$_instance->isDownloaded()) {
                if (!class_exists('Magento', false)) {
                    if (!file_exists(self::$_instance->getBootstrapPath())) {
                        return false;
                    }
                    include_once self::$_instance->getBootstrapPath();

                    \Magento\App\ObjectManager::getInstance()
                        ->get('Magento\App\State')
                        ->setIsDownloader();
                }
                \Magento\App\ObjectManager::getInstance()->get('Magento\Core\Model\App');
                if (self::isInstalled()) {
                    \Mage::getSingleton('Magento\Backend\Model\UrlInterface')->turnOffSecretKey();
                }
            }
        }
        return self::$_instance;
    }

    /**
     * Retrieve Downloader root dir
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->_rootDir;
    }

    /**
     * Retrieve Magento root dir
     *
     * @return string
     */
    public function getMageDir()
    {
        return $this->_mageDir;
    }

    /**
     * Retrieve Mage Class file path
     *
     * @return string
     */
    public function getBootstrapPath()
    {
        return $this->getMageDir() . '/app/bootstrap.php';
    }

    /**
     * Retrieve path for \Magento\Profiler
     *
     * @return string
     */
    public function getVarFilename()
    {
        return $this->getMageDir() . '/lib/Magento/Profiler.php';
    }

    /**
     * Retrieve downloader file path
     *
     * @param string $name
     * @return string
     */
    public function filepath($name = '')
    {
        return rtrim($this->getRootDir() . '/' . $name, '/');
    }

    /**
     * Retrieve object of view
     *
     * @return \Magento\Downloader\View
     */
    public function view()
    {
        if (!$this->_view) {
            $this->_view = new \Magento\Downloader\View;
        }
        return $this->_view;
    }

    /**
     * Retrieve object of model
     *
     * @param string $model
     * @param boolean $singleton
     * @return \Magento\Downloader\Model
     */
    public function model($model = null, $singleton = false)
    {
        if ($singleton && isset($this->_singletons[$model])) {
            return $this->_singletons[$model];
        }

        if (is_null($model)) {
            $class = 'Magento\Downloader\Model';
        } else {
            $class = 'Magento\Downloader\Model\\' . str_replace(' ', '\\', ucwords(str_replace('\\', ' ', $model)));
            if (!class_exists($class, false)) {
                include_once str_replace('_', '/', $class).'.php';
            }
        }

        $object = new $class();

        if ($singleton) {
            $this->_singletons[$model] = $object;
        }

        return $object;
    }

    /**
     * Retrieve object of config
     *
     * @return \Magento\Connect\Config
     */
    public function config()
    {
        if (!$this->_config) {
            $this->_config = $this->model('connect', true)->connect()->getConfig();
            if (!$this->_config->isLoaded()) {
                $this->session()->addMessage('error', "Settings has not been loaded. Used default settings");
                if ($this->_config->getError()) {
                    $this->session()->addMessage('error', $this->_config->getError());
                }
            }
        }
        return $this->_config;
    }

    /**
     * Retrieve object of channel config
     *
     * @return \Magento\Downloader\Model\Config\ConfigInterface
     */
    public function channelConfig()
    {
        if (!$this->_localConfig) {
            $this->_localConfig = $this->model('config', true)->getChannelConfig();
        }
        return $this->_localConfig;
    }

    /**
     * Retrieve object of session
     *
     * @return \Magento\Downloader\Model\Session
     */
    public function session()
    {
        if (!$this->_session) {
            $this->_session = $this->model('session')->start();
        }
        return $this->_session;
    }

    /**
     * Set Controller action
     *
     * @param string $action
     * @return \Magento\Downloader\Controller
     */
    public function setAction($action=null)
    {
        if (is_null($action)) {
            if (!empty($this->_action)) {
                return $this;
            }
            $action = !empty($_GET[self::ACTION_KEY]) ? $_GET[self::ACTION_KEY] : 'index';
        }
        if (empty($action) || !is_string($action) || !method_exists($this, $this->getActionMethod($action))) {
            //$action = 'noroute';
            $action = 'index';
        }
        $this->_action = $action;
        return $this;
    }

    /**
     * Retrieve Controller action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Set Redirect to URL
     *
     * @param string $url
     * @param bool $force
     * @return \Magento\Downloader\Controller
     */
    public function redirect($url, $force = false)
    {
        $this->_redirectUrl = $url;
        if ($force) {
            $this->processRedirect();
        }
        return $this;
    }

    /**
     * Precess redirect
     *
     * @return \Magento\Downloader\Controller
     */
    public function processRedirect()
    {
        if ($this->_redirectUrl) {
            if (headers_sent()) {
                echo '<script type="text/javascript">location.href="' . $this->_redirectUrl . '"</script>';
                exit;
            } else {
                header("Location: " . $this->_redirectUrl);
                exit;
            }
        }
        return $this;
    }

    /**
     * Forward to action
     *
     * @param string $action
     * @return \Magento\Downloader\Controller
     */
    public function forward($action)
    {
        $this->setAction($action);
        $this->_isDispatched = false;
        return $this;
    }

    /**
     * Retrieve action method by action name
     *
     * @param string $action
     * @return string
     */
    public function getActionMethod($action = null)
    {
        $method = (!is_null($action) ? $action : $this->_action) . 'Action';
        return $method;
    }

    /**
     * Generate URL for action
     *
     * @param string $action
     * @param array $params
     */
    public function url($action = '', $params = array())
    {
        $args = array();
        foreach ($params as $k => $v) {
            $args[] = sprintf('%s=%s', rawurlencode($k), rawurlencode($v));
        }
        $args = $args ? join('&', $args) : '';

        return sprintf('%s?%s=%s%s', $_SERVER['SCRIPT_NAME'], self::ACTION_KEY, rawurlencode($action), $args);
    }

    /**
     * Dispatch process
     */
    public function dispatch()
    {
        header('Content-type: text/html; charset=UTF-8');

        $this->setAction();

        if (!$this->isInstalled()) {
            if (!in_array($this->getAction(), array('index', 'connectInstallAll', 'empty', 'cleanCache'))) {
                $this->setAction('index');
            }
        } else {
            $this->session()->authenticate();
        }

        while (!$this->_isDispatched) {
            $this->_isDispatched = true;

            $method = $this->getActionMethod();
            $this->$method();
        }

        $this->processRedirect();
    }

    /**
     * Check root dir is writable
     *
     * @return bool
     */
    public function isWritable()
    {
        if (is_null($this->_writable)) {
            $this->_writable = is_writable($this->getMageDir() . '/')
                && is_writable($this->filepath())
                && (!file_exists($this->filepath('config.ini') || is_writable($this->filepath('config.ini'))));
        }
        return $this->_writable;
    }

    /**
     * Check is Magento files downloaded
     *
     * @return bool
     */
    public function isDownloaded()
    {
        return file_exists($this->getBootstrapPath()) && file_exists($this->getVarFilename());
    }

    /**
     * Check is Magento installed
     *
     * @return bool
     */
    public function isInstalled()
    {
        if (!$this->isDownloaded()) {
            return false;
        }
        return \Mage::isInstalled();
    }

    /**
     * Retrieve Maintenance flag
     *
     * @return bool
     */
    protected function _getMaintenanceFlag()
    {
        if (is_null($this->_maintenance)) {
            $this->_maintenance = !empty($_REQUEST['maintenance']) && $_REQUEST['maintenance'] == '1' ? true : false;
        }
        return $this->_maintenance;
    }

    /**
     * Retrieve Maintenance Flag file path
     *
     * @return string
     */
    protected function _getMaintenanceFilePath()
    {
        if (is_null($this->_maintenanceFile)) {
            $path = dirname(dirname(__DIR__)) . '/';
            $this->_maintenanceFile = $path . 'maintenance.flag';
        }
        return $this->_maintenanceFile;
    }

    /**
     * Begin install package(s)
     */
    public function startInstall()
    {
        if ($this->_getMaintenanceFlag()) {
            $maintenance_filename='maintenance.flag';
            $config = $this->config();
            if (!$this->isWritable() || strlen($config->__get('remote_config')) > 0) {
                $ftpObj = new \Magento\Connect\Ftp();
                $ftpObj->connect($config->__get('remote_config'));
                $tempFile = tempnam(sys_get_temp_dir(),'maintenance');
                @file_put_contents($tempFile, 'maintenance');
                $ftpObj->upload($maintenance_filename, $tempFile);
                $ftpObj->close();
            } else {
                @file_put_contents($this->_getMaintenanceFilePath(), 'maintenance');
            }
        }

        if (!empty($_GET['archive_type'])) {

            $backupName = $_GET['backup_name'];
            $connect = $this->model('connect', true)->connect();
            $isSuccess = true;

            if (!preg_match('/^[a-zA-Z0-9\ ]{0,50}$/', $backupName)) {
                $connect->runHtmlConsole('Please use only letters (a-z or A-Z), numbers (0-9) or space in '
                    . 'Backup Name field. Other characters are not allowed.');
                $isSuccess = false;
            }

            if ($isSuccess) {
                $isSuccess = $this->_createBackup($_GET['archive_type'], $_GET['backup_name']);
            }

            if (!$isSuccess) {
                $this->endInstall();
                $this->cleanCache();
                throw new \Magento\Exception(
                    'The installation process has been canceled because of the backup creation error'
                );
            }
        }
    }

    /**
     * End install package(s)
     */
    public function endInstall()
    {
        //$connect
        /** @var $connect \Magento\Downloader\Model\Connect */
        $frontend = $this->model('connect', true)->connect()->getFrontend();
        if (!($frontend instanceof \Magento\Downloader\Connect\Frontend)) {
            $this->cleanCache();
        }
    }

    protected function cleanCache()
    {
        $result = true;
        $message = '';
        try {
            if ($this->isInstalled()) {
                if (!empty($_REQUEST['clean_sessions'])) {
                    \Mage::app()->cleanAllSessions();
                    $message .= 'Session cleaned successfully. ';
                }
                \Mage::app()->cleanCache();

                // reinit config and apply all updates
                \Mage::app()->getConfig()->reinit();

                /** @var $updater \Magento\Module\UpdaterInterface*/
                $updater = \Magento\App\ObjectManager::getInstance()
                    ->get('Magento\Module\UpdaterInterface');
                $updater->updateScheme();
                $updater->updateData();
                $message .= 'Cache cleaned successfully';
            } else {
                $result = true;
            }
        } catch (\Exception $e) {
            $result = false;
            $message = "Exception during cache and session cleaning: ".$e->getMessage();
            $this->session()->addMessage('error', $message);
        }

        if ($result && $this->_getMaintenanceFlag()) {
            $maintenance_filename='maintenance.flag';
            $config = $this->config();
            if (!$this->isWritable() && strlen($config->__get('remote_config')) > 0) {
                $ftpObj = new \Magento\Connect\Ftp();
                $ftpObj->connect($config->__get('remote_config'));
                $ftpObj->delete($maintenance_filename);
                $ftpObj->close();
            } else {
                @unlink($this->_getMaintenanceFilePath());
            }
        }
        return array('result' => $result, 'message' => $message);
    }

    /**
     * Gets the current Magento Connect Manager (Downloader) version string
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return string
     */
    public static function getVersion()
    {
        $i = self::getVersionInfo();
        return trim(
            "{$i['major']}.{$i['minor']}.{$i['revision']}"
                . ($i['patch'] != '' ? ".{$i['patch']}" : "")
                . "-{$i['stability']}{$i['number']}",
            '.-'
        );
    }

    /**
     * Gets the detailed Magento Connect Manager (Downloader) version information
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return array
     */
    public static function getVersionInfo()
    {
        return array(
            'major'     => '1',
            'minor'     => '5',
            'revision'  => '0',
            'patch'     => '0',
            'stability' => 'rc',
            'number'    => '2',
        );
    }

    /**
     * Create Backup
     *
     * @param string $archiveType
     * @param string $archiveName
     * @return bool
     */
    protected function _createBackup($archiveType, $archiveName){
        /** @var $connect \Magento\Downloader\Connect */
        $connect = $this->model('connect', true)->connect();
        $connect->runHtmlConsole('Creating backup...');

        $isSuccess = false;

        try {
            $type = $this->_getBackupTypeByCode($archiveType);

            $backupManager = \Magento\Core\Model\ObjectManager::getInstance()->get('Magento\Backup\Factory')
                ->create($type)
                ->setBackupExtension($this->_getExtensionType($type))
                ->setTime(time())
                ->setName($archiveName)
                ->setBackupsDir(\Mage::getBaseDir('var') . '/backups');

            \Magento\App\ObjectManager::getInstance()
                ->get('Magento\Registry')
                ->register('backup_manager', $backupManager);

            if ($type != \Magento\Backup\Factory::TYPE_DB) {
                $backupManager->setRootDir(\Mage::getBaseDir())
                    ->addIgnorePaths($this->_getBackupIgnorePaths());
            }
            $backupManager->create();
            $connect->runHtmlConsole(
                $this->_getCreateBackupSuccessMessageByType($type)
            );
            $isSuccess = true;
        } catch (\Magento\Backup\Exception\NotEnoughFreeSpace $e) {
            $connect->runHtmlConsole('Not enough free space to create backup.');
        } catch (\Magento\Backup\Exception\NotEnoughPermissions $e) {
            $connect->runHtmlConsole('Not enough permissions to create backup.');
        } catch (\Exception  $e) {
            $connect->runHtmlConsole('An error occurred while creating the backup.');
        }

        return $isSuccess;
    }

    protected function _getExtensionType($type)
    {
        $extensionType = array(
            \Magento\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => 'tgz',
            \Magento\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'tgz',
            \Magento\Backup\Factory::TYPE_MEDIA => 'tgz',
            \Magento\Backup\Factory::TYPE_DB => 'gz'
        );

        return $extensionType[$type];
    }

    protected function _getBackupIgnorePaths()
    {
        return array(
            '.git',
            '.svn',
            'maintenance.flag',
            \Mage::getBaseDir('var') . '/session',
            \Mage::getBaseDir('var') . '/cache',
            \Mage::getBaseDir('var') . '/full_page_cache',
            \Mage::getBaseDir('var') . '/locks',
            \Mage::getBaseDir('var') . '/log',
            \Mage::getBaseDir('var') . '/report'
        );
    }

    /**
     * Retrieve Backup Type by Code
     *
     * @param int $code
     * @return string
     */
    protected function _getBackupTypeByCode($code)
    {
        $typeMap = array(
            1 => \Magento\Backup\Factory::TYPE_DB,
            2 => \Magento\Backup\Factory::TYPE_SYSTEM_SNAPSHOT,
            3 => \Magento\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA,
            4 => \Magento\Backup\Factory::TYPE_MEDIA
        );

        if (!isset($typeMap[$code])) {
            \Mage::throwException('Unknown backup type');
        }

        return $typeMap[$code];
    }

    /**
     * Get backup create success message by backup type
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateBackupSuccessMessageByType($type)
    {
        $messagesMap = array(
            \Magento\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => 'System backup has been created',
            \Magento\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'System (excluding Media) backup has been created',
            \Magento\Backup\Factory::TYPE_MEDIA => 'Database and media backup has been created',
            \Magento\Backup\Factory::TYPE_DB => 'Database backup has been created'
        );

        if (!isset($messagesMap[$type])) {
            return '';
        }

        return $messagesMap[$type];
    }
}
