<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento application for performance tests
 */
namespace Magento\ToolkitFramework;

use Magento\Framework\App\Filesystem\DirectoryList;

class Application
{
    /**
     * Area code
     */
    const AREA_CODE = 'adminhtml';

    /**
     * Fixtures directory
     */
    const FIXTURES_DIR = '/../../../fixtures';

    /**
     * Fixtures file name pattern
     */
    const FIXTURE_PATTERN = '*.php';

    /**
     * Application object
     *
     * @var \Magento\Framework\AppInterface
     */
    protected $_application;

    /**
     * @var \Magento\Framework\Shell
     */
    protected $_shell;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * List of fixtures applied to the application
     *
     * @var \Magento\ToolkitFramework\Fixture[]
     */
    protected $_fixtures = [];

    /**
     * Parameters labels
     *
     * @var array
     */
    protected $_paramLabels = [];

    /**
     * @var string
     */
    protected $_applicationBaseDir;

    /**
     * @var array
     */
    protected $_initArguments;

    /**
     * @param string $applicationBaseDir
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct($applicationBaseDir, \Magento\Framework\Shell $shell, array $initArguments)
    {
        $this->_applicationBaseDir = $applicationBaseDir;
        $this->_shell = $shell;
        $this->_initArguments = $initArguments;
    }

    /**
     * Update permissions for `var` directory
     *
     * @return void
     */
    protected function _updateFilesystemPermissions()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $varDirectory */
        $varDirectory = $this->getObjectManager()->get('Magento\Framework\Filesystem')
            ->getDirectoryWrite(DirectoryList::VAR_DIR);
        $varDirectory->changePermissions('', 0777);
    }

    /**
     * Bootstrap application, so it is possible to use its resources
     *
     * @return \Magento\ToolkitFramework\Application
     */
    protected function _bootstrap()
    {
        $this->getObjectManager()->configure(
            $this->getObjectManager()->get('Magento\Framework\App\ObjectManager\ConfigLoader')->load(self::AREA_CODE)
        );
        $this->getObjectManager()->get('Magento\Framework\Config\ScopeInterface')->setCurrentScope(self::AREA_CODE);
        return $this;
    }

    /**
     * Bootstrap
     *
     * @return Application
     */
    public function bootstrap()
    {
        return $this->_bootstrap();
    }

    /**
     * Run reindex
     *
     * @return Application
     */
    public function reindex()
    {
        $this->_shell->execute(
            'php -f ' . $this->_applicationBaseDir . '/dev/shell/indexer.php -- reindexall'
        );
        return $this;
    }

    /**
     * Load fixtures
     *
     * @return $this
     * @throws \Exception
     */
    public function loadFixtures()
    {
        if (!is_readable(__DIR__ . self::FIXTURES_DIR)) {
            throw new \Exception(
                'Fixtures set directory `' . __DIR__ . self::FIXTURES_DIR . '` is not readable or does not exists.'
            );
        }
        $files = glob(__DIR__ . self::FIXTURES_DIR . DIRECTORY_SEPARATOR . self::FIXTURE_PATTERN);
        foreach ($files as $file) {
            /** @var \Magento\ToolkitFramework\Fixture $fixture */
            $fixture = require realpath($file);
            $this->_fixtures[$fixture->getPriority()] = $fixture;
        }
        ksort($this->_fixtures);
        foreach ($this->_fixtures as $fixture) {
            $this->_paramLabels = array_merge($this->_paramLabels, $fixture->introduceParamLabels());
        }
        return $this;
    }

    /**
     * Get param labels
     *
     * @return array
     */
    public function getParamLabels()
    {
        return $this->_paramLabels;
    }

    /**
     * Get fixtures
     *
     * @return Fixture[]
     */
    public function getFixtures()
    {
        return $this->_fixtures;
    }

    /**
     * Get object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        if (!$this->_objectManager) {
            $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(
                BP,
                $this->_initArguments
            );
            $this->_objectManager = $objectManagerFactory->create($this->_initArguments);
            $this->_objectManager->get('Magento\Framework\App\State')->setAreaCode(self::AREA_CODE);
        }
        return $this->_objectManager;
    }

    /**
     * Reset object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function resetObjectManager()
    {
        $this->_objectManager = null;
        $this->bootstrap();
        return $this;
    }
}
