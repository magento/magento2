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

use Magento\Framework\App\Console\Response;
use Magento\Framework\App\Bootstrap;

class Console implements \Magento\Framework\AppInterface
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
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    protected $_loader;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $_response;

    /**
     * @param \Magento\Install\Model\Installer\ConsoleFactory $installerFactory
     * @param \Magento\Install\App\Output $output
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\App\ObjectManager\ConfigLoader $loader
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param Response $response
     * @param array $arguments
     */
    public function __construct(
        \Magento\Install\Model\Installer\ConsoleFactory $installerFactory,
        \Magento\Install\App\Output $output,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\ObjectManager\ConfigLoader $loader,
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\App\Filesystem $filesystem,
        Response $response,
        array $arguments = array()
    ) {
        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->_loader = $loader;
        $this->_state = $state;
        $this->_installerFactory = $installerFactory;
        $this->_arguments = $arguments;
        $this->_output = $output;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
    }

    /**
     * Install application
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

        $result = $installer->install($this->_arguments);

        if (!$installer->hasErrors()) {
            $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
            $this->_output->success($msg . PHP_EOL);
        } else {
            $this->_output->error(implode(PHP_EOL, $installer->getErrors()) . PHP_EOL);
        }
    }

    /**
     * Run application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $areaCode = 'install';
        $this->_state->setAreaCode($areaCode);
        $this->_objectManager->configure($this->_loader->load($areaCode));

        /** @var \Magento\Install\Model\Installer\Console $installer */
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

    /**
     * {@inheritdoc}
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
