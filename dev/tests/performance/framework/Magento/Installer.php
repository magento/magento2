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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento application for performance tests
 */
class Magento_Installer
{
    /**
     * @var string
     */
    protected $_installerScript;

    /**
     * @var Magento_Shell
     */
    protected $_shell;

    /**
     * Constructor
     *
     * @param string $installerScript
     * @param Magento_Shell $shell
     * @throws Magento_Exception
     */
    public function __construct($installerScript, Magento_Shell $shell)
    {
        if (!is_file($installerScript)) {
            throw new Magento_Exception("File '$installerScript' is not found.");
        }

        $this->_installerScript = realpath($installerScript);
        $this->_shell = $shell;
    }

    /**
     * Uninstall application
     */
    public function uninstall()
    {
        $this->_shell->execute('php -f %s -- --uninstall', array($this->_installerScript));
    }

    /**
     * Install application according to installation options and apply fixtures
     *
     * @param array $options
     * @param array $fixtureFiles
     */
    public function install(array $options, array $fixtureFiles = array())
    {
        $this->_install($options);
        $this->_bootstrap();
        $this->_applyFixtures($fixtureFiles);
        $this->_reindex();
        $this->_updateFilesystemPermissions();
    }

    /**
     * Perform installation of Magento app
     *
     * @param array $options
     */
    protected function _install($options)
    {
        $installCmd = 'php -f %s --';
        $installCmdArgs = array($this->_installerScript);
        foreach ($options as $optionName => $optionValue) {
            $installCmd .= " --$optionName %s";
            $installCmdArgs[] = $optionValue;
        }
        $this->_shell->execute($installCmd, $installCmdArgs);
    }

    /**
     * Bootstrap installed application
     */
    protected function _bootstrap()
    {
        Mage::app();
    }

    /**
     * Apply fixture scripts
     *
     * @param array $fixtureFiles
     */
    protected function _applyFixtures(array $fixtureFiles)
    {
        foreach ($fixtureFiles as $oneFixtureFile) {
            require $oneFixtureFile;
        }
    }

    /**
     * Run all indexer processes
     */
    protected function _reindex()
    {
        /** @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getModel('Mage_Index_Model_Indexer');
        /** @var $process Mage_Index_Model_Process */
        foreach ($indexer->getProcessesCollection() as $process) {
            if ($process->getIndexer()->isVisible()) {
                $process->reindexEverything();
            }
        }
    }

    /**
     * Update permissions for `var` directory
     */
    protected function _updateFilesystemPermissions()
    {
        Varien_Io_File::chmodRecursive(Mage::getBaseDir('var'), 0777);
    }
}
