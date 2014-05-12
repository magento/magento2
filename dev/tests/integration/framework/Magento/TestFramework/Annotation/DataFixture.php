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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of the @magentoDataFixture DocBlock annotation
 */
namespace Magento\TestFramework\Annotation;

class DataFixture
{
    /**
     * @var string
     */
    protected $_fixtureBaseDir;

    /**
     * Fixtures that have been applied
     *
     * @var array
     */
    private $_appliedFixtures = array();

    /**
     * Constructor
     *
     * @param string $fixtureBaseDir
     * @throws \Magento\Framework\Exception
     */
    public function __construct($fixtureBaseDir)
    {
        if (!is_dir($fixtureBaseDir)) {
            throw new \Magento\Framework\Exception("Fixture base directory '{$fixtureBaseDir}' does not exist.");
        }
        $this->_fixtureBaseDir = realpath($fixtureBaseDir);
    }

    /**
     * Handler for 'startTestTransactionRequest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @param \Magento\TestFramework\Event\Param\Transaction $param
     */
    public function startTestTransactionRequest(
        \PHPUnit_Framework_TestCase $test,
        \Magento\TestFramework\Event\Param\Transaction $param
    ) {
        /* Start transaction before applying first fixture to be able to revert them all further */
        if ($this->_getFixtures('method', $test)) {
            /* Re-apply even the same fixtures to guarantee data consistency */
            if ($this->_appliedFixtures) {
                $param->requestTransactionRollback();
            }
            $param->requestTransactionStart();
        } else if (!$this->_appliedFixtures && $this->_getFixtures('class', $test)) {
            $param->requestTransactionStart();
        }
    }

    /**
     * Handler for 'endTestNeedTransactionRollback' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @param \Magento\TestFramework\Event\Param\Transaction $param
     */
    public function endTestTransactionRequest(
        \PHPUnit_Framework_TestCase $test,
        \Magento\TestFramework\Event\Param\Transaction $param
    ) {
        /* Isolate other tests from test-specific fixtures */
        if ($this->_appliedFixtures && $this->_getFixtures('method', $test)) {
            $param->requestTransactionRollback();
        }
    }

    /**
     * Handler for 'startTransaction' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     */
    public function startTransaction(\PHPUnit_Framework_TestCase $test)
    {
        $this->_applyFixtures($this->_getFixtures('method', $test) ?: $this->_getFixtures('class', $test));
    }

    /**
     * Handler for 'rollbackTransaction' event
     */
    public function rollbackTransaction()
    {
        $this->_revertFixtures();
    }

    /**
     * Retrieve fixtures from annotation
     *
     * @param string $scope 'class' or 'method'
     * @param \PHPUnit_Framework_TestCase $test
     * @return array
     * @throws \Magento\Framework\Exception
     */
    protected function _getFixtures($scope, \PHPUnit_Framework_TestCase $test)
    {
        $annotations = $test->getAnnotations();
        $result = array();
        if (!empty($annotations[$scope]['magentoDataFixture'])) {
            foreach ($annotations[$scope]['magentoDataFixture'] as $fixture) {
                if (strpos($fixture, '\\') !== false) {
                    // usage of a single directory separator symbol streamlines search across the source code
                    throw new \Magento\Framework\Exception(
                        'Directory separator "\\" is prohibited in fixture declaration.'
                    );
                }
                $fixtureMethod = array(get_class($test), $fixture);
                if (is_callable($fixtureMethod)) {
                    $result[] = $fixtureMethod;
                } else {
                    $result[] = $this->_fixtureBaseDir . '/' . $fixture;
                }
            }
        }
        return $result;
    }

    /**
     * Execute single fixture script
     *
     * @param string|array $fixture
     */
    protected function _applyOneFixture($fixture)
    {
        try {
            if (is_callable($fixture)) {
                call_user_func($fixture);
            } else {
                require $fixture;
            }
        } catch (\Exception $e) {
            echo 'Error in fixture: ', json_encode($fixture), PHP_EOL, $e;
        }
    }

    /**
     * Execute fixture scripts if any
     *
     * @param array $fixtures
     * @throws \Magento\Framework\Exception
     */
    protected function _applyFixtures(array $fixtures)
    {
        try {
            /* Execute fixture scripts */
            foreach ($fixtures as $oneFixture) {
                /* Skip already applied fixtures */
                if (in_array($oneFixture, $this->_appliedFixtures, true)) {
                    continue;
                }
                $this->_applyOneFixture($oneFixture);
                $this->_appliedFixtures[] = $oneFixture;
            }
        } catch (\PDOException $e) {
            echo $e;
        }
    }

    /**
     * Revert changes done by fixtures
     */
    protected function _revertFixtures()
    {
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
                $rollbackScript = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '_rollback' . $extension;
                if (file_exists($rollbackScript)) {
                    $this->_applyOneFixture($rollbackScript);
                }
            }
        }
        $this->_appliedFixtures = array();
    }
}
