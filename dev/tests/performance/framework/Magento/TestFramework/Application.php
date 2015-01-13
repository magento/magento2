<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento application for performance tests
 */
namespace Magento\TestFramework;

use Magento\Framework\App\Filesystem\DirectoryList;

class Application
{
    /**
     * Configuration object
     *
     * @var \Magento\TestFramework\Performance\Config
     */
    protected $_config;

    /**
     * Path to shell installer and uninstaller script
     *
     * @var string
     */
    protected $_script;

    /**
     * @var \Magento\Framework\Shell
     */
    protected $_shell;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
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
    protected $_fixtures = [];

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Performance\Config $config
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct(
        \Magento\TestFramework\Performance\Config $config,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Shell $shell
    ) {
        $shellDir = $config->getApplicationBaseDir() . '/setup';
        $this->_objectManager = $objectManager;
        $this->_script = $this->_assertPath($shellDir . '/index.php');
        $this->_config = $config;
        $this->_shell = $shell;
    }

    /**
     * Asserts that a file exists and returns its real path
     *
     * @param string $path
     * @return string
     * @throws \Magento\Framework\Exception
     */
    private function _assertPath($path)
    {
        if (!is_file($path)) {
            throw new \Magento\Framework\Exception("File '{$path}' is not found.");
        }
        return realpath($path);
    }

    /**
     * Reset application - i.e. cleanup already installed app, or install it otherwise
     *
     * @return \Magento\TestFramework\Application
     */
    protected function _reset()
    {
        if ($this->_config->getInstallOptions()) {
            $this->_uninstall()->_install()->reindex()->_updateFilesystemPermissions();
        } else {
            $this->_isInstalled = true;
        }
        return $this;
    }

    /**
     * Reset application (uninstall, install, reindex, update permissions)
     *
     * @return Application
     */
    public function reset()
    {
        return $this->_reset();
    }

    /**
     * Run reindex
     *
     * @return Application
     */
    public function reindex()
    {
        $this->_shell->execute(
            'php -f ' . $this->_config->getApplicationBaseDir() . '/dev/shell/indexer.php -- reindexall'
        );
        return $this;
    }

    /**
     * Uninstall application
     *
     * @return \Magento\TestFramework\Application
     */
    protected function _uninstall()
    {
        $this->_shell->execute('php -f %s uninstall', [$this->_script]);

        $this->_isInstalled = false;
        $this->_fixtures = [];

        return $this;
    }

    /**
     * Install application according to installation options
     *
     * @return \Magento\TestFramework\Application
     * @throws \Magento\Framework\Exception
     */
    protected function _install()
    {
        $installOptions = $this->_config->getInstallOptions();
        $installOptionsNoValue = $this->_config->getInstallOptionsNoValue();
        if (!$installOptions) {
            throw new \Magento\Framework\Exception('Trying to install Magento, but installation options are not set');
        }

        // Populate install options with global options
        $baseUrl = 'http://' . $this->_config->getApplicationUrlHost() . $this->_config->getApplicationUrlPath();
        $installOptions = array_merge($installOptions, ['base_url' => $baseUrl, 'base_url_secure' => $baseUrl]);
        $installCmd = 'php -f %s install';
        $installCmdArgs = [$this->_script];
        foreach ($installOptions as $optionName => $optionValue) {
            $installCmd .= " --{$optionName}=%s";
            $installCmdArgs[] = $optionValue;
        }
        foreach ($installOptionsNoValue as $optionName) {
            $installCmd .= " --{$optionName}";
        }
        $this->_shell->execute($installCmd, $installCmdArgs);

        $this->_isInstalled = true;
        $this->_fixtures = [];
        return $this;
    }

    /**
     * Update permissions for `var` directory
     */
    protected function _updateFilesystemPermissions()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $varDirectory */
        $varDirectory = $this->getObjectManager()->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );
        $varDirectory->changePermissions('', 0777);
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

        foreach ($fixturesToApply as $fixtureFile) {
            $this->applyFixture($fixtureFile);
        }
        $this->_fixtures = $fixtures;

        $this->reindex()->_updateFilesystemPermissions();
    }

    /**
     * Apply fixture file
     *
     * @param string $fixtureFilename
     */
    public function applyFixture($fixtureFilename)
    {
        require $fixtureFilename;
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
     * Get object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->_objectManager;
    }
}
