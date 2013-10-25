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
 * @package     Magento_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Installation wizard controller
 */
namespace Magento\Install\Controller;

class Wizard extends \Magento\Install\Controller\Action
{
    /**
     * Installer Model
     *
     * @var \Magento\Install\Model\Installer
     */
    protected $_installer;

    /**
     * Install Wizard
     *
     * @var \Magento\Install\Model\Wizard
     */
    protected $_wizard;

    /**
     * Install Session
     *
     * @var \Magento\Core\Model\Session\Generic
     */
    protected $_session;

    /**
     * DB Updater
     *
     * @var \Magento\App\UpdaterInterface
     */
    protected $_dbUpdater;

    /**
     * Store Manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Config\Scope $configScope
     * @param \Magento\View\DesignInterface $viewDesign
     * @param \Magento\Core\Model\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\App $app
     * @param \Magento\App\State $appState
     * @param \Magento\Install\Model\Installer $installer
     * @param \Magento\Install\Model\Wizard $wizard
     * @param \Magento\Core\Model\Session\Generic $session
     * @param \Magento\App\UpdaterInterface $dbUpdater
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Config\Scope $configScope,
        \Magento\View\DesignInterface $viewDesign,
        \Magento\Core\Model\Theme\CollectionFactory $collectionFactory,
        \Magento\Core\Model\App $app,
        \Magento\App\State $appState,
        \Magento\Install\Model\Installer $installer,
        \Magento\Install\Model\Wizard $wizard,
        \Magento\Core\Model\Session\Generic $session,
        \Magento\App\UpdaterInterface $dbUpdater,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $configScope, $viewDesign, $collectionFactory, $app, $appState);
        $this->_installer = $installer;
        $this->_wizard = $wizard;
        $this->_session = $session;
        $this->_dbUpdater = $dbUpdater;
        $this->_storeManager = $storeManager;
    }


    /**
     * Perform necessary checks for all actions
     *
     * Redirect out if system is already installed
     * Throw a bootstrap exception if page cannot be displayed due to mis-configured base directories
     *
     * @throws \Magento\BootstrapException
     */
    public function preDispatch()
    {
        if ($this->_appState->isInstalled()) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->_redirect('/');
            return;
        }

        $this->setFlag('', self::FLAG_NO_CHECK_INSTALLATION, true);
        return parent::preDispatch();
    }

    /**
     * Retrieve installer object
     *
     * @return \Magento\Install\Model\Installer
     */
    protected function _getInstaller()
    {
        return $this->_installer;
    }

    /**
     * Retrieve wizard
     *
     * @return \Magento\Install\Model\Wizard
     */
    protected function _getWizard()
    {
        return $this->_wizard;
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Install\Controller\Wizard
     */
    protected function _prepareLayout()
    {
        $this->loadLayout('install_wizard');
        $step = $this->_getWizard()->getStepByRequest($this->getRequest());
        if ($step) {
            $step->setActive(true);
        }

        $this->getLayout()->addBlock('Magento\Install\Block\State', 'install.state', 'left');
        return $this;
    }

    /**
     * Checking installation status
     *
     * @return bool
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function _checkIfInstalled()
    {
        if ($this->_getInstaller()->isApplicationInstalled()) {
            $this->getResponse()
                ->setRedirect($this->_storeManager->getStore()->getBaseUrl())
                ->sendResponse();
            exit;
        }
        return true;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_forward('begin');
    }

    /**
     * Begin installation action
     */
    public function beginAction()
    {
        $this->_checkIfInstalled();

        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Magento\Install\Model\Session');

        $this->getLayout()->addBlock('Magento\Install\Block\Begin', 'install.begin', 'content');

        $this->renderLayout();
    }

    /**
     * Process begin step POST data
     */
    public function beginPostAction()
    {
        $this->_checkIfInstalled();

        $agree = $this->getRequest()->getPost('agree');
        if ($agree && $step = $this->_getWizard()->getStepByName('begin')) {
            $this->getResponse()->setRedirect($step->getNextUrl());
        } else {
            $this->_redirect('install');
        }
    }

    /**
     * Localization settings
     */
    public function localeAction()
    {
        $this->_checkIfInstalled();
        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Magento\Install\Model\Session');
        $this->getLayout()->addBlock('Magento\Install\Block\Locale', 'install.locale', 'content');

        $this->renderLayout();
    }

    /**
     * Change current locale
     */
    public function localeChangeAction()
    {
        $this->_checkIfInstalled();

        $locale = $this->getRequest()->getParam('locale');
        $timezone = $this->getRequest()->getParam('timezone');
        $currency = $this->getRequest()->getParam('currency');
        if ($locale) {
            $this->_session->setLocale($locale)
                ->setTimezone($timezone)
                ->setCurrency($currency);
        }

        $this->_redirect('*/*/locale');
    }

    /**
     * Saving localization settings
     */
    public function localePostAction()
    {
        $this->_checkIfInstalled();
        $step = $this->_getWizard()->getStepByName('locale');

        $data = $this->getRequest()->getPost('config');
        if ($data) {
            $this->_session->setLocaleData($data);
        }

        $this->getResponse()->setRedirect($step->getNextUrl());
    }

    /**
     * Download page action
     */
    public function downloadAction()
    {
        $this->_checkIfInstalled();
        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Magento\Install\Model\Session');
        $this->getLayout()->addBlock('Magento\Install\Block\Download', 'install.download', 'content');

        $this->renderLayout();
    }

    /**
     * Download post action
     */
    public function downloadPostAction()
    {
        $this->_checkIfInstalled();
        switch ($this->getRequest()->getPost('continue')) {
            case 'auto':
                $this->_forward('downloadAuto');
                break;

            case 'manual':
                $this->_forward('downloadManual');
                break;

            case 'svn':
                $step = $this->_getWizard()->getStepByName('download');
                $this->getResponse()->setRedirect($step->getNextUrl());
                break;

            default:
                $this->_redirect('*/*/download');
                break;
        }
    }

    /**
     * Download auto action
     */
    public function downloadAutoAction()
    {
        $step = $this->_getWizard()->getStepByName('download');
        $this->getResponse()->setRedirect($step->getNextUrl());
    }

    /**
     * Install action
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function installAction()
    {
        $pear = \Magento\Pear::getInstance();        
        $params = array(
            'comment' => __("Downloading and installing Magento, please wait...") . "\r\n\r\n"
        );
        if ($this->getRequest()->getParam('do')) {
            $state = $this->getRequest()->getParam('state', 'beta');
            if ($state) {
                $result = $pear->runHtmlConsole(array(
                'comment'   => __("Setting preferred state to: %1", $state) . "\r\n\r\n",
                'command'   => 'config-set',
                'params'    => array('preferred_state', $state)
                ));
                if ($result instanceof PEAR_Error) {
                    $this->installFailureCallback();
                    exit;
                }
            }
            $params['command'] = 'install';
            $params['options'] = array('onlyreqdeps' => 1);
            $params['params'] = $this->_objectManager->get('Magento\Install\Model\Installer\Pear')->getPackages();
            $params['success_callback'] = array($this, 'installSuccessCallback');
            $params['failure_callback'] = array($this, 'installFailureCallback');
        }
        $pear->runHtmlConsole($params);
        $this->_frontController->getResponse()->clearAllHeaders();
    }

    /**
     * Install success callback
     */
    public function installSuccessCallback()
    {
        echo 'parent.installSuccess()';
    }

    /**
     * Install failure callback
     */
    public function installFailureCallback()
    {
        echo 'parent.installFailure()';
    }

    /**
     * Download manual action
     */
    public function downloadManualAction()
    {
        $step = $this->_getWizard()->getStepByName('download');
        $this->getResponse()->setRedirect($step->getNextUrl());
    }

    /**
     * Configuration data installation
     */
    public function configAction()
    {
        $this->_checkIfInstalled();
        $this->_getInstaller()->checkServer();

        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        $data = $this->getRequest()->getQuery('config');
        if ($data) {
            $this->_session->setLocaleData($data);
        }

        $this->_prepareLayout();
        $this->_initLayoutMessages('Magento\Install\Model\Session');
        $this->getLayout()->addBlock('Magento\Install\Block\Config', 'install.config', 'content');

        $this->renderLayout();
    }

    /**
     * Process configuration POST data
     */
    public function configPostAction()
    {
        $this->_checkIfInstalled();
        $step = $this->_getWizard()->getStepByName('config');

        $config             = $this->getRequest()->getPost('config');
        $connectionConfig   = $this->getRequest()->getPost('connection');

        if ($config && $connectionConfig && isset($connectionConfig[$config['db_model']])) {

            $data = array_merge($config, $connectionConfig[$config['db_model']]);

            $this->_session
                ->setConfigData($data)
                ->setSkipUrlValidation($this->getRequest()->getPost('skip_url_validation'))
                ->setSkipBaseUrlValidation($this->getRequest()->getPost('skip_base_url_validation'));
            try {
                $this->_getInstaller()->installConfig($data);
                $this->_redirect('*/*/installDb');
                return $this;
            } catch (\Exception $e) {
                $this->_session->addError($e->getMessage());
                $this->getResponse()->setRedirect($step->getUrl());
            }
        }
        $this->getResponse()->setRedirect($step->getUrl());
    }

    /**
     * Install DB
     */
    public function installDbAction()
    {
        $this->_checkIfInstalled();
        $step = $this->_getWizard()->getStepByName('config');
        try {
            $this->_getInstaller()->installDb();
            /**
             * Clear session config data
             */
            $this->_session->getConfigData(true);

            $this->_storeManager->getStore()->resetConfig();
            $this->_dbUpdater->updateData();

            $this->getResponse()->setRedirect($step->getNextUrl());
        } catch (\Exception $e) {
            $this->_session->addError($e->getMessage());
            $this->getResponse()->setRedirect($step->getUrl());
        }
    }

    /**
     * Install administrator account
     */
    public function administratorAction()
    {
        $this->_checkIfInstalled();

        $this->_prepareLayout();
        $this->_initLayoutMessages('Magento\Install\Model\Session');

        $this->getLayout()->addBlock('Magento\Install\Block\Admin', 'install.administrator', 'content');
        $this->renderLayout();
    }

    /**
     * Process administrator installation POST data
     */
    public function administratorPostAction()
    {
        $this->_checkIfInstalled();

        $step = $this->_wizard->getStepByName('administrator');
        $adminData      = $this->getRequest()->getPost('admin');
        $encryptionKey  = $this->getRequest()->getPost('encryption_key');

        try {
            $encryptionKey = $this->_getInstaller()->getValidEncryptionKey($encryptionKey);
            $this->_getInstaller()->createAdministrator($adminData);
            $this->_getInstaller()->installEncryptionKey($encryptionKey);
            $this->getResponse()->setRedirect($step->getNextUrl());
        } catch (\Exception $e) {
            $this->_session->setAdminData($adminData);
            if ($e instanceof \Magento\Core\Exception) {
                $this->_session->addMessages($e->getMessages());
            } else {
                $this->_session->addError($e->getMessage());
            }
            $this->getResponse()->setRedirect($step->getUrl());
        }
    }

    /**
     * End installation
     */
    public function endAction()
    {
        $this->_checkIfInstalled();

        if ($this->_appState->isInstalled()) {
            $this->_redirect('*/*');
            return;
        }

        $this->_getInstaller()->finish();

        $this->_objectManager->get('Magento\AdminNotification\Model\Survey')->saveSurveyViewed(true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Magento\Install\Model\Session');

        $this->getLayout()->addBlock('Magento\Install\Block\End', 'install.end', 'content');
        $this->renderLayout();
        $this->_session->clear();
    }
}
