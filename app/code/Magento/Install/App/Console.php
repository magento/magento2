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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\App;

use Magento\App\Console\Response;

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
     * @var \Magento\Filesystem\Directory\Read
     */
    protected $rootDirectory;

    /**
     * @var \Magento\App\Console\Response
     */
    protected $_response;

    /**
     * @param \Magento\Install\Model\Installer\ConsoleFactory $installerFactory
     * @param \Magento\Install\App\Output $output
     * @param \Magento\App\State $state
     * @param \Magento\App\ObjectManager\ConfigLoader $loader
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\Filesystem $filesystem
     * @param Response $response
     * @param array $arguments
     */
    public function __construct(
        \Magento\Install\Model\Installer\ConsoleFactory $installerFactory,
        \Magento\Install\App\Output $output,
        \Magento\App\State $state,
        \Magento\App\ObjectManager\ConfigLoader $loader,
        \Magento\ObjectManager $objectManager,
        \Magento\App\Filesystem $filesystem,
        Response $response,
        array $arguments = array()
    ) {
        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
        $this->_loader = $loader;
        $this->_state = $state;
        $this->_installerFactory = $installerFactory;
        $this->_arguments = $this->_buildInitArguments($arguments);
        $this->_output = $output;
        $this->_response = $response;
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
        $directories = array();
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_URIS])) {
            $uris = unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_URIS]));
            foreach ($uris as $code => $uri) {
                $args[\Magento\App\Filesystem::PARAM_APP_DIRS][$code]['uri'] = $uri;
            }
        }
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS])) {
            $dirs = unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS]));
            foreach ($dirs as $code => $dir) {
                $args[\Magento\App\Filesystem::PARAM_APP_DIRS][$code]['path'] = $dir;
            }
        }
        return $args;
    }

    /**
     * Install/Uninstall application
     *
     * @param \Magento\Install\Model\Installer\Console $installer
     * @return void
     */
    protected function _handleInstall(\Magento\Install\Model\Installer\Console $installer)
    {
        if (isset(
            $this->_arguments['config']
        ) && $this->rootDirectory->isExist(
            $this->rootDirectory->getRelativePath($this->_arguments['config'])
        )
        ) {
            $config = (array)include $this->_arguments['config'];
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
                $msg = $result ? 'Uninstalled successfully' : 'Ignoring attempt to uninstall non-installed application';
            } else {
                $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
            }
            $this->_output->success($msg . PHP_EOL);
        } else {
            $this->_output->error(implode(PHP_EOL, $installer->getErrors()) . PHP_EOL);
        }
    }

    /**
     * Run application
     *
     * @return \Magento\App\ResponseInterface
     */
    public function launch()
    {
        $areaCode = 'install';
        $this->_state->setAreaCode($areaCode);
        $this->_objectManager->configure($this->_loader->load($areaCode));

        $installer = $this->_installerFactory->create(array('installArgs' => $this->_arguments));
        if (isset($this->_arguments['show_locales'])) {
            $this->_output->readableOutput($this->_output->prepareArray($installer->getAvailableLocales()));
        } elseif (isset($this->_arguments['show_currencies'])) {
            $this->_output->readableOutput($this->_output->prepareArray($installer->getAvailableCurrencies()));
        } elseif (isset($this->_arguments['show_timezones'])) {
            $this->_output->readableOutput($this->_output->prepareArray($installer->getAvailableTimezones()));
        } elseif (isset($this->_arguments['show_install_options'])) {
            $this->_output->readableOutput(PHP_EOL . 'Required parameters:');
            $this->_output->readableOutput($this->_output->alignArrayKeys($installer->getRequiredParams()));
            $this->_output->readableOutput(PHP_EOL . 'Optional parameters:');
            $this->_output->readableOutput($this->_output->alignArrayKeys($installer->getOptionalParams()));
            $this->_output->readableOutput(
                PHP_EOL .
                'Flag values are considered positive if set to 1, y, true or yes.' .
                'Any other value is considered as negative.' .
                PHP_EOL
            );
        } else {
            $this->_handleInstall($installer);
        }
        $this->_response->setCode(0);
        return $this->_response;
    }
}
