<?php
/**
 * Console entry point
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\EntryPoint;

class Console extends \Magento\Core\Model\AbstractEntryPoint
{
    /**
     * Application params
     *
     * @var array
     */
    protected $_params = array();

    /**
     * @param \Magento\Core\Model\Config\Primary $baseDir
     * @param array $params
     * @param \Magento\Core\Model\Config\Primary $config
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Install\Model\EntryPoint\Output $output
     */
    public function __construct(
        $baseDir,
        array $params = array(),
        \Magento\Core\Model\Config\Primary $config = null,
        \Magento\ObjectManager $objectManager = null,
        \Magento\Install\Model\EntryPoint\Output $output = null
    ) {
        $this->_params = $this->_buildInitParams($params);
        if (!$config) {
            $config = new \Magento\Core\Model\Config\Primary($baseDir, $this->_params);
        }
        $this->_output = $output ?: new \Magento\Install\Model\EntryPoint\Output();
        parent::__construct($config, $objectManager);
    }

    /**
     * Customize application init parameters
     *
     * @param array $args
     * @return array
     */
    protected function _buildInitParams(array $args)
    {
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_URIS])) {
            $args[\Magento\Core\Model\App::PARAM_APP_URIS] =
                unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_URIS]));
        }
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS])) {
            $args[\Magento\Core\Model\App::PARAM_APP_DIRS] =
                unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS]));
        }
        return $args;
    }

    /**
     * Run http application
     */
    protected function _processRequest()
    {
        /**
         * @var $installer \Magento\Install\Model\Installer\Console
         */
        $installer = $this->_objectManager->create(
            'Magento\Install\Model\Installer\Console',
            array('installArgs' => $this->_params)
        );
        if (isset($this->_params['show_locales'])) {
            $this->_output->export($installer->getAvailableLocales());
        } else if (isset($this->_params['show_currencies'])) {
            $this->_output->export($installer->getAvailableCurrencies());
        } else if (isset($this->_params['show_timezones'])) {
            $this->_output->export($installer->getAvailableTimezones());
        } else if (isset($this->_params['show_install_options'])) {
            $this->_output->export($installer->getAvailableInstallOptions());
        } else {
            $this->_handleInstall($installer);
        }
    }

    /**
     * Install/Uninstall application
     *
     * @param \Magento\Install\Model\Installer\Console $installer
     */
    protected function _handleInstall(\Magento\Install\Model\Installer\Console $installer)
    {
        if (isset($this->_params['config']) && file_exists($this->_params['config'])) {
            $config = (array) include($this->_params['config']);
            $this->_params = array_merge((array)$config, $this->_params);
        }
        $isUninstallMode = isset($this->_params['uninstall']);
        if ($isUninstallMode) {
            $result = $installer->uninstall();
        } else {
            $result = $installer->install($this->_params);
        }
        if (!$installer->hasErrors()) {
            if ($isUninstallMode) {
                $msg = $result ?
                    'Uninstalled successfully' :
                    'Ignoring attempt to uninstall non-installed application';
            } else {
                $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
            }
            $this->_output->success($msg . PHP_EOL);
        } else {
            $this->_output->error(implode(PHP_EOL, $installer->getErrors()) . PHP_EOL);
        }
    }
}
