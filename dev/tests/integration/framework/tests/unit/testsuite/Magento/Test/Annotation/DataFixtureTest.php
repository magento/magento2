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
namespace Magento\Test\Annotation;

/**
 * Test class for \Magento\TestFramework\Annotation\DataFixture.
 *
 * @magentoDataFixture sampleFixtureOne
 */
class DataFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\DataFixture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->getMock(
            'Magento\TestFramework\Annotation\DataFixture',
            array('_applyOneFixture'),
            array(__DIR__ . '/_files')
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

    /**
     * @expectedException \Magento\Framework\Exception
     */
    public function testConstructorException()
    {
        new \Magento\TestFramework\Annotation\DataFixture(__DIR__ . '/non_existing_fixture_dir');
    }

    public function testStartTestTransactionRequestClassAnnotation()
    {
        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTransaction($this);
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testStartTestTransactionRequestMethodAnnotation()
    {
        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTransaction($this);
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
        $this->assertTrue($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDataFixture fixture\path\must\not\contain\backslash.php
     * @expectedException \Magento\Framework\Exception
     */
    public function testStartTestTransactionRequestInvalidPath()
    {
        $this->_object->startTestTransactionRequest($this, new \Magento\TestFramework\Event\Param\Transaction());
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testEndTestTransactionRequestMethodAnnotation()
    {
        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTransaction($this);
        $this->_object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertTrue($eventParam->isTransactionRollbackRequested());
    }

    public function testStartTransactionClassAnnotation()
    {
        $this->_object->expects($this->once())->method('_applyOneFixture')->with(array(__CLASS__, 'sampleFixtureOne'));
        $this->_object->startTransaction($this);
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testStartTransactionMethodAnnotation()
    {
        $this->_object->expects($this->at(0))->method('_applyOneFixture')->with(array(__CLASS__, 'sampleFixtureTwo'));
        $this->_object->expects(
            $this->at(1)
        )->method(
            '_applyOneFixture'
        )->with(
            $this->stringEndsWith('path/to/fixture/script.php')
        );
        $this->_object->startTransaction($this);
    }

    /**
     * @magentoDataFixture sampleFixtureOne
     * @magentoDataFixture sampleFixtureTwo
     */
    public function testRollbackTransactionRevertFixtureMethod()
    {
        $this->_object->startTransaction($this);
        $this->_object->expects(
            $this->once()
        )->method(
            '_applyOneFixture'
        )->with(
            array(__CLASS__, 'sampleFixtureTwoRollback')
        );
        $this->_object->rollbackTransaction();
    }

    /**
     * @magentoDataFixture path/to/fixture/script.php
     * @magentoDataFixture sample_fixture_two.php
     */
    public function testRollbackTransactionRevertFixtureFile()
    {
        $this->_object->startTransaction($this);
        $this->_object->expects(
            $this->once()
        )->method(
            '_applyOneFixture'
        )->with(
            $this->stringEndsWith('sample_fixture_two_rollback.php')
        );
        $this->_object->rollbackTransaction();
    }
}
