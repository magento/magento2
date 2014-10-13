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

namespace Magento\ToolkitFramework;

/**
 * Class ApplicationTest
 *
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Shell|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\ToolkitFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_applicationBaseDir;

    /**
     * Set Up before test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_applicationBaseDir = __DIR__ . '/../../../../../bootstrap.php';
        $this->_shell = $this->getMock('Magento\Framework\Shell', array('execute'), array(), '', false);

        $this->_object = new \Magento\ToolkitFramework\Application($this->_applicationBaseDir, $this->_shell);

        $this->_object->applied = array(); // For fixture testing
    }

    /**
     * Tear down after test
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->_shell);
        unset($this->_object);
    }

    /**
     * Apply fixtures test
     *
     * @param array $fixtures
     * @param array $expected
     * @dataProvider applyFixturesDataProvider
     *
     * @return void
     */
    public function testApplyFixtures($fixtures, $expected)
    {
        $this->_object->applyFixtures($fixtures);
        $this->assertEquals($expected, $this->_object->applied);
    }

    /**
     * Apply fixture data provider
     *
     * @return array
     */
    public function applyFixturesDataProvider()
    {
        return array(
            'empty fixtures' => array(
                array(),
                array()
            ),
            'fixtures' => array(
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                array('fixture1', 'fixture2')
            ),
        );
    }

    /**
     * Apply fixture test
     *
     * @param array $initialFixtures
     * @param array $subsequentFixtures
     * @param array $subsequentExpected
     * @dataProvider applyFixturesSeveralTimesDataProvider
     *
     * @return void
     */
    public function testApplyFixturesSeveralTimes($initialFixtures, $subsequentFixtures, $subsequentExpected)
    {
        $this->_object->applyFixtures($initialFixtures);
        $this->_object->applied = array();
        $this->_object->applyFixtures($subsequentFixtures);
        $this->assertEquals($subsequentExpected, $this->_object->applied);
    }

    /**
     * Apply fixture data provider
     *
     * @return array
     */
    public function applyFixturesSeveralTimesDataProvider()
    {
        return array(
            'no fixtures applied, when sets are same' => array(
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                array()
            ),
            'missing fixture applied for a super set' => array(
                $this->_getFixtureFiles(array('fixture1')),
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                array('fixture2')
            ),
            'no fixtures applied, when sets were exist before' => array(
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                $this->_getFixtureFiles(array('fixture1')),
                array()
            ),
        );
    }

    /**
     * Get fixture files
     *
     * @param array $fixtures
     *
     * @return array
     */
    protected function _getFixtureFiles($fixtures)
    {
        $result = array();
        foreach ($fixtures as $fixture) {
            $result[] = __DIR__ . "/_files/application_test/{$fixture}.php";
        }
        return $result;
    }
}
