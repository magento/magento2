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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of the @magentoDataFixture doc comment directive
 */
class Magento_Test_Listener_Annotation_Fixture
{
    /**
     * @var Magento_Test_Listener
     */
    protected $_listener;

    /**
     * Fixtures that have been applied
     *
     * @var array
     */
    private $_appliedFixtures = array();

    /**
     * Constructor
     *
     * @param Magento_Test_Listener $listener
     */
    public function __construct(Magento_Test_Listener $listener)
    {
        $this->_listener = $listener;
    }

    /**
     * Handler for 'endTestSuite' event
     */
    public function endTestSuite()
    {
        $this->_revertFixtures();
    }

    /**
     * Handler for 'startTest' event
     */
    public function startTest()
    {
        /* Apply fixtures declared on test case (class) and test (method) levels */
        $methodFixtures = $this->_getFixtures('method');
        if ($methodFixtures) {
            /* Re-apply even the same fixtures to guarantee data consistency */
            $this->_revertFixtures();
            $this->_applyFixtures($methodFixtures);
        } else {
            $this->_applyFixtures($this->_getFixtures('class'));
        }
    }

    /**
     * Handler for 'endTest' event
     */
    public function endTest()
    {
        /* Isolate other tests from test-specific fixtures */
        $methodFixtures = $this->_getFixtures('method');
        if ($methodFixtures) {
            $this->_revertFixtures();
        }
    }

    /**
     * Retrieve fixtures from annotation
     *
     * @param string $scope 'class' or 'method'
     * @return array
     */
    protected function _getFixtures($scope)
    {
        $annotations = $this->_listener->getCurrentTest()->getAnnotations();
        if (!empty($annotations[$scope]['magentoDataFixture'])) {
            return $annotations[$scope]['magentoDataFixture'];
        }
        return array();
    }

    /**
     * Check whether the same connection is being used for both read and write operations
     *
     * @return bool
     */
    protected function _isSingleConnection()
    {
        $readAdapter  = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('read');
        $writeAdapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        return ($readAdapter === $writeAdapter);
    }

    /**
     * Start transaction
     */
    protected function _startTransaction()
    {
        /** @var $adapter Varien_Db_Adapter_Interface */
        $adapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        $adapter->beginTransparentTransaction();
    }

    /**
     * Rollback transaction
     */
    protected function _rollbackTransaction()
    {
        /** @var $adapter Varien_Db_Adapter_Interface */
        $adapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        $adapter->rollbackTransparentTransaction();
    }

    /**
     * Execute single fixture script
     *
     * @param string|array $fixture
     */
    protected function _applyOneFixture($fixture)
    {
        if (is_callable($fixture)) {
            call_user_func($fixture);
        } else {
            require($fixture);
        }
    }

    /**
     * Execute fixture scripts if any
     *
     * @param array $fixtures
     * @throws Magento_Exception
     */
    protected function _applyFixtures(array $fixtures)
    {
        if (empty($fixtures)) {
            return;
        }
        /* Start transaction before applying first fixture to be able to revert them all further */
        if (empty($this->_appliedFixtures)) {
            if (!$this->_isSingleConnection()) {
                throw new Magento_Exception('Transaction fixtures with 2 connections are not implemented yet.');
            }
            $this->_startTransaction();
        }
        /* Execute fixture scripts */
        foreach ($fixtures as $fixture) {
            if (strpos($fixture, '\\') !== false) {
                throw new Magento_Exception('The "\" symbol is not allowed for fixture definition.');
            }
            $fixtureMethod = array(get_class($this->_listener->getCurrentTest()), $fixture);
            $fixtureScript = realpath(dirname(__FILE__) . '/../../../../../testsuite') . DIRECTORY_SEPARATOR . $fixture;
            /* Skip already applied fixtures */
            if (in_array($fixtureMethod, $this->_appliedFixtures, true)
                || in_array($fixtureScript, $this->_appliedFixtures, true)
            ) {
                continue;
            }
            if (is_callable($fixtureMethod)) {
                $this->_applyOneFixture($fixtureMethod);
                $this->_appliedFixtures[] = $fixtureMethod;
            } else {
                $this->_applyOneFixture($fixtureScript);
                $this->_appliedFixtures[] = $fixtureScript;
            }
        }
    }

    /**
     * Revert changes done by fixtures
     */
    protected function _revertFixtures()
    {
        if (empty($this->_appliedFixtures)) {
            return;
        }
        $this->_rollbackTransaction();
        foreach ($this->_appliedFixtures as $fixture) {
            if (is_callable($fixture)) {
                $fixture[1] .= 'Rollback';
                if (is_callable($fixture)) {
                    $this->_applyOneFixture($fixture);
                }
            } else {
                $fileInfo = pathinfo($fixture);
                $extension = '';
                if (isset($fileInfo['extension'])) {
                    $extension = '.' . $fileInfo['extension'];
                }
                $rollbackFile = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '_rollback' . $extension;
                if (file_exists($rollbackFile)) {
                    $this->_applyOneFixture($rollbackFile);
                }
            }
        }
        $this->_appliedFixtures = array();
    }
}
