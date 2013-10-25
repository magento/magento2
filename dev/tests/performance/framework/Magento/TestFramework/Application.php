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
 * @package     performance_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento application for performance tests
 */
namespace Magento\TestFramework;

class Application
{
    /**
     * Configuration object
     *
     * @param \Magento\Config
     */
    protected $_config;

    /**
     * Path to shell installer script
     *
     * @var string
     */
    protected $_installerScript;

    /**
     * @var \Magento\Shell
     */
    protected $_shell;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Whether application is installed
     *
     * @var bool
     */
    protected $_isInstalled = false;

    /**
     * List of fixtures applied to the application
     *
     * @var array
     */
    protected $_fixtures = array();

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Performance\Config $config
     * @param \Magento\Shell $shell
     * @throws \Magento\Exception
     */
    public function __construct(\Magento\TestFramework\Performance\Config $config, \Magento\Shell $shell)
    {
        $installerScript = $config->getApplicationBaseDir() . '/dev/shell/install.php';
        if (!is_file($installerScript)) {
            throw new \Magento\Exception("File '$installerScript' is not found.");
        }
        $this->_installerScript = realpath($installerScript);
        $this->_config = $config;
        $this->_shell = $shell;
    }

    /**
     * Reset application - i.e. cleanup already installed app, or install it otherwise
     *
     * @return \Magento\TestFramework\Application
     */
    protected function _reset()
    {
        if ($this->_config->getInstallOptions()) {
            $this->_uninstall()
                ->_install()
                ->_reindex()
                ->_updateFilesystemPermissions();
        } else {
            $this->_isInstalled = true;
        }
        return $this;
    }

    /**
     * Uninstall application
     *
     * @return \Magento\TestFramework\Application
     */
    protected function _uninstall()
    {
        $this->_shell->execute('php -f %s -- --uninstall', array($this->_installerScript));

        $this->_isInstalled = false;
        $this->_fixtures = array();

        return $this;
    }

    /**
     * Install application according to installation options
     *
     * @return \Magento\TestFramework\Application
     * @throws \Magento\Exception
     */
    protected function _install()
    {
        $installOptions = $this->_config->getInstallOptions();
        if (!$installOptions) {
            throw new \Magento\Exception('Trying to install Magento, but installation options are not set');
        }

        // Populate install options with global options
        $baseUrl = 'http://' . $this->_config->getApplicationUrlHost() . $this->_config->getApplicationUrlPath();
        $installOptions = array_merge($installOptions, array('url' => $baseUrl, 'secure_base_url' => $baseUrl));
        $adminOptions = $this->_config->getAdminOptions();
        foreach ($adminOptions as $key => $val) {
            $installOptions['admin_' . $key] = $val;
        }

        $installCmd = 'php -f %s --';
        $installCmdArgs = array($this->_installerScript);
        foreach ($installOptions as $optionName => $optionValue) {
            $installCmd .= " --$optionName %s";
            $installCmdArgs[] = $optionValue;
        }
        $this->_shell->execute($installCmd, $installCmdArgs);

        $this->_isInstalled = true;
        $this->_fixtures = array();
        return $this;
    }

    /**
     * Run all indexer processes
     *
     * @return \Magento\TestFramework\Application
     */
    protected function _reindex()
    {
        $this->_bootstrap();

        /** @var $indexer \Magento\Index\Model\Indexer */
        $indexer = $this->_objectManager->create('Magento\Index\Model\Indexer');
        /** @var $process \Magento\Index\Model\Process */
        foreach ($indexer->getProcessesCollection() as $process) {
            if ($process->getIndexer()->isVisible()) {
                $process->reindexEverything();
            }
        }

        return $this;
    }

    /**
     * Update permissions for `var` directory
     */
    protected function _updateFilesystemPermissions()
    {
        \Magento\Io\File::chmodRecursive($this->_objectManager->get('Magento\App\Dir')->getDir('var'), 0777);
    }

    /**
     * Bootstrap application, so it is possible to use its resources
     *
     * @return \Magento\TestFramework\Application
     */
    protected function _bootstrap()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = new \Magento\Core\Model\ObjectManager(
                new \Magento\Core\Model\Config\Primary(BP, $_SERVER)
            );
        }
        /** @var $app \Magento\Core\Model\App */
        $this->_objectManager->get('Magento\Core\Model\App');
        return $this;
    }

    /**
     * Work on application, so that it has all and only $fixtures applied. May require reinstall, if
     * excessive fixtures has been applied before.
     *
     * @param array $fixtures
     */
    public function applyFixtures(array $fixtures)
    {
        if (!$this->_isInstalled || $this->_doFixturesNeedReinstall($fixtures)) {
            $this->_reset();
        }

        // Apply fixtures
        $fixturesToApply = array_diff($fixtures, $this->_fixtures);
        if (!$fixturesToApply) {
            return;
        }

        $this->_bootstrap();
        foreach ($fixturesToApply as $fixtureFile) {
            require $fixtureFile;
        }
        $this->_fixtures = $fixtures;

        $this->_reindex()
            ->_updateFilesystemPermissions();
    }

    /**
     * Compare list of fixtures needed to be set to the application, with the list of fixtures already in it.
     * Return, whether application reinstall (cleanup) is needed to properly apply the fixtures.
     *
     * @param array $fixtures
     * @return bool
     */
    protected function _doFixturesNeedReinstall($fixtures)
    {
        $excessiveFixtures = array_diff($this->_fixtures, $fixtures);
        return (bool)$excessiveFixtures;
    }

    /**
     * @return \Magento\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }
}
