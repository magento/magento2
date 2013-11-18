<?php
/**
 * Console application
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
namespace Magento\Install\App;

class Console implements \Magento\AppInterface
{
    /**
     * @var  \Magento\Install\Model\Installer\ConsoleFactory
     */
    protected $_installerFactory;

    /** @var array */
    protected $_arguments;

    /** @var \Magento\Install\App\Output */
    protected $_output;

    /**
     * @var \Magento\App\ObjectManager\ConfigLoader
     */
    protected $_loader;

    /**
     * @var \Magento\App\State
     */
    protected $_state;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\Install\Model\Installer\ConsoleFactory $installerFactory
     * @param Output $output
     * @param \Magento\App\State $state
     * @param \Magento\App\ObjectManager\ConfigLoader $loader
     * @param \Magento\ObjectManager $objectManager
     * @param array $arguments
     */
    public function __construct(
        \Magento\Install\Model\Installer\ConsoleFactory $installerFactory,
        \Magento\Install\App\Output $output,
        \Magento\App\State $state,
        \Magento\App\ObjectManager\ConfigLoader $loader,
        \Magento\ObjectManager $objectManager,
        array $arguments = array()
    ) {
        $this->_loader = $loader;
        $this->_state  = $state;
        $this->_installerFactory = $installerFactory;
        $this->_arguments = $this->_buildInitArguments($arguments);
        $this->_output = $output;
        $this->_objectManager = $objectManager;
    }

    /**
     * Customize application init arguments
     *
     * @param array $args
     * @return array
     */
    protected function _buildInitArguments(array $args)
    {
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_URIS])) {
            $args[\Magento\App\Dir::PARAM_APP_URIS] =
                unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_URIS]));
        }
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS])) {
            $args[\Magento\App\Dir::PARAM_APP_DIRS] =
                unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS]));
        }
        return $args;
    }

    /**
     * Install/Uninstall application
     *
     * @param \Magento\Install\Model\Installer\Console $installer
     */
    protected function _handleInstall(\Magento\Install\Model\Installer\Console $installer)
    {
        if (isset($this->_arguments['config']) && file_exists($this->_arguments['config'])) {
            $config = (array) include($this->_arguments['config']);
            $this->_arguments = array_merge((array)$config, $this->_arguments);
        }
        $isUninstallMode = isset($this->_arguments['uninstall']);
        if ($isUninstallMode) {
            $result = $installer->uninstall();
        } else {
            $result = $installer->install($this->_arguments);
        }
        if (!$installer->hasErrors()) {
            if ($isUninstallMode) {
                $msg = $result ? 'Uninstalled successfully'
                    : 'Ignoring attempt to uninstall non-installed application';
            } else {
                $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
            }
            $this->_output->success($msg . PHP_EOL);
        } else {
            $this->_output->error(implode(PHP_EOL, $installer->getErrors()) . PHP_EOL);
        }
    }

    /**
     * Execute application
     * @return int
     */
    public function execute()
    {
        $areaCode = 'install';
        $this->_state->setAreaCode($areaCode);
        $this->_objectManager->configure($this->_loader->load($areaCode));

        $installer = $this->_installerFactory->create(array('installArgs' => $this->_arguments));
        if (isset($this->_arguments['show_locales'])) {
            $this->_output->export($installer->getAvailableLocales());
        } else if (isset($this->_arguments['show_currencies'])) {
            $this->_output->export($installer->getAvailableCurrencies());
        } else if (isset($this->_arguments['show_timezones'])) {
            $this->_output->export($installer->getAvailableTimezones());
        } else if (isset($this->_arguments['show_install_options'])) {
            $this->_output->export($installer->getAvailableInstallOptions());
        } else {
            $this->_handleInstall($installer);
        }
        return 0;
    }
}
