<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Annotation;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Test class for \Magento\TestFramework\Annotation\DataFixture.
 *
 * @magentoDataFixture sampleFixtureOne
 */
class DataFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\DataFixture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->getMockBuilder(\Magento\TestFramework\Annotation\DataFixture::class)
            ->setMethods(['_applyOneFixture'])
            ->setConstructorArgs([__DIR__ . '/_files'])
            ->getMock();
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
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
        $this->assertTrue($eventParam->isTransactionStartRequested());
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
        $this->assertFalse($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testDisabledDbIsolation()
    {
        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTransaction($this);
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDataFixture fixture\path\must\not\contain\backslash.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
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
        $this->_object->expects($this->once())->method('_applyOneFixture')->with([__CLASS__, 'sampleFixtureOne']);
        $this->_object->startTransaction($this);
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testStartTransactionMethodAnnotation()
    {
        $this->_object->expects($this->at(0))->method('_applyOneFixture')->with([__CLASS__, 'sampleFixtureTwo']);
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
            [__CLASS__, 'sampleFixtureTwoRollback']
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

    /**
     * @magentoDataFixture Foo_DataFixtureDummy::Test/Integration/foo.php
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testModuleDataFixture()
    {
        ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Foo_DataFixtureDummy', __DIR__);
        $this->_object->expects($this->once())->method('_applyOneFixture')
            ->with(__DIR__ . '/Test/Integration/foo.php');
        $this->_object->startTransaction($this);
    }
}
