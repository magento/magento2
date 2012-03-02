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

class Magento_Test_Listener_Annotation_FixtureTestSingleConnection extends Magento_Test_Listener_Annotation_Fixture
{
    protected function _isSingleConnection()
    {
        return true;
    }
}

/**
 * Test class for Magento_Test_Listener_Annotation_Fixture.
 *
 * @magentoDataFixture sampleFixtureOne
 */
class Magento_Test_Listener_Annotation_FixtureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Listener
     */
    protected $_listener;

    /**
     * @var Magento_Test_Listener_Annotation_Fixture|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_annotation;

    protected function setUp()
    {
        $this->_listener = new Magento_Test_Listener;
        $this->_listener->startTest($this);

        $this->_annotation = $this->getMock(
            'Magento_Test_Listener_Annotation_FixtureTestSingleConnection',
            array('_startTransaction', '_rollbackTransaction', '_applyOneFixture'),
            array($this->_listener)
        );
    }

    public static function sampleFixtureOne()
    {
    }

    public static function sampleFixtureTwo()
    {
    }

    public static function sampleFixtureTwoRollback()
    {
    }

    public function testClassAnnotation()
    {
        $this->_annotation
            ->expects($this->at(0))
            ->method('_startTransaction')
        ;
        $this->_annotation
            ->expects($this->at(1))
            ->method('_applyOneFixture')
            ->with(array(__CLASS__, 'sampleFixtureOne'))
        ;
        $this->_annotation->startTest();

        return $this->_annotation;
    }

    /**
     * @param Magento_Test_Listener_Annotation_Fixture $annotation
     * @depends testClassAnnotation
     */
    public function testClassAnnotationShared($annotation)
    {
        $this->_annotation
            ->expects($this->never())
            ->method('_applyOneFixture')
        ;
        $annotation->startTest();
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testMethodAnnotation()
    {
        $this->_annotation
            ->expects($this->at(0))
            ->method('_startTransaction')
        ;
        $this->_annotation
            ->expects($this->at(1))
            ->method('_applyOneFixture')
            ->with(array(__CLASS__, 'sampleFixtureTwo'))
        ;
        $this->_annotation
            ->expects($this->at(2))
            ->method('_applyOneFixture')
            ->with($this->stringEndsWith('path/to/fixture/script.php'))
        ;
        $this->_annotation->startTest();

        $this->_annotation
            ->expects($this->once())
            ->method('_rollbackTransaction')
        ;
        $this->_annotation->endTest();
    }

    /**
     * @magentoDataFixture fixture\path\must\not\contain\backslash.php
     * @expectedException Magento_Exception
     */
    public function testMethodAnnotationInvalidPath()
    {
        $this->_annotation->startTest();
    }

    /**
     * @param Magento_Test_Listener_Annotation_Fixture $annotation
     * @depends testClassAnnotation
     */
    public function testEndTestSuite($annotation)
    {
        $annotation
            ->expects($this->once())
            ->method('_rollbackTransaction')
        ;
        $annotation->endTestSuite();
    }


    /**
     * @magentoDataFixture sampleFixtureOne
     * @magentoDataFixture sampleFixtureTwo
     * @covers Magento_Test_Listener_Annotation_Fixture::_revertFixture
     */
    public function testRevertFixtureMethod()
    {
        $this->_annotation->startTest();

        $this->_annotation
            ->expects($this->at(1))
            ->method('_applyOneFixture')
            ->with(array(__CLASS__, 'sampleFixtureTwoRollback'))
        ;

        $this->_annotation->endTest();
    }


    /**
     * @magentoDataFixture path/to/fixture/script.php
     * @magentoDataFixture ../framework/tests/unit/testsuite/Magento/Test/Listener/_files/sample_fixture_two.php
     * @covers Magento_Test_Listener_Annotation_Fixture::_revertFixture
     */
    public function testRevertFixtureFile()
    {
        $this->_annotation->startTest();

        $this->_annotation
            ->expects($this->at(1))
            ->method('_applyOneFixture')
            ->with($this->stringEndsWith('Magento/Test/Listener/_files/sample_fixture_two_rollback.php'));

        $this->_annotation->endTest();
    }
}
