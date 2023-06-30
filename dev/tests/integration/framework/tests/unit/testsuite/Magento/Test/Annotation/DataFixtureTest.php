<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Annotation;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Annotation\DataFixture;
use Magento\TestFramework\Event\Param\Transaction;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Annotation\TestsIsolation;

/**
 * Test class for \Magento\TestFramework\Annotation\DataFixture.
 *
 * @magentoDataFixture sampleFixtureOne
 */
class DataFixtureTest extends TestCase
{
    /**
     * @var DataFixture|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $object;

    /**
     * @var TestsIsolation|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $testsIsolationMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->object = $this->getMockBuilder(DataFixture::class)
            ->setMethods(['_applyOneFixture', 'getComponentRegistrar', 'getTestKey'])
            ->getMock();
        $this->testsIsolationMock = $this->getMockBuilder(TestsIsolation::class)
            ->setMethods(['createDbSnapshot', 'checkTestIsolation'])
            ->getMock();
        /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager->expects($this->atLeastOnce())->method('get')->with(TestsIsolation::class)
            ->willReturn($this->testsIsolationMock);
        \Magento\TestFramework\Helper\Bootstrap::setObjectManager($objectManager);

        $directory = __DIR__;
        if (!defined('INTEGRATION_TESTS_DIR')) {
            define('INTEGRATION_TESTS_DIR', dirname($directory, 4));
        }
    }

    /**
     * Dummy fixture
     *
     * @return void
     */
    public static function sampleFixtureOne(): void
    {
    }

    /**
     * Dummy fixture
     *
     * @return void
     */
    public static function sampleFixtureTwo(): void
    {
    }

    /**
     * Dummy fixture rollback
     *
     * @return void
     */
    public static function sampleFixtureTwoRollback(): void
    {
    }

    /**
     * @return void
     */
    public function testStartTestTransactionRequestClassAnnotation(): void
    {
        $this->createResolverMock();
        $eventParam = new Transaction();
        $this->object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new Transaction();
        $this->object->startTransaction($this);
        $this->object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     *
     * @return void
     */
    public function testStartTestTransactionRequestMethodAnnotation(): void
    {
        $this->createResolverMock();
        $eventParam = new Transaction();
        $this->object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new Transaction();
        $this->object->startTransaction($this);
        $this->object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     *
     * @return void
     */
    public function testDisabledDbIsolation(): void
    {
        $this->createResolverMock();
        $eventParam = new Transaction();
        $this->object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new Transaction();
        $this->object->startTransaction($this);
        $this->object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     *
     * @return void
     */
    public function testEndTestTransactionRequestMethodAnnotation(): void
    {
        $this->createResolverMock();
        $eventParam = new Transaction();
        $this->object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new Transaction();
        $this->object->startTransaction($this);
        $this->object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertTrue($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @return void
     */
    public function testStartTransactionClassAnnotation(): void
    {
        $this->createResolverMock();
        $this->object->expects($this->once())
            ->method('_applyOneFixture')
            ->with([__CLASS__, 'sampleFixtureOne']);
        $this->object->startTransaction($this);
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     *
     * @return void
     */
    public function testStartTransactionMethodAnnotation(): void
    {
        $this->createResolverMock();
        $this->object->expects($this->at(0))
            ->method('_applyOneFixture')
            ->with([__CLASS__, 'sampleFixtureTwo']);
        $this->object->expects(
            $this->at(1)
        )->method(
            '_applyOneFixture'
        )->with(
            $this->stringEndsWith('path/to/fixture/script.php')
        );
        $this->object->startTransaction($this);
    }

    /**
     * @magentoDataFixture sampleFixtureOne
     * @magentoDataFixture sampleFixtureTwo
     *
     * @return void
     */
    public function testRollbackTransactionRevertFixtureMethod(): void
    {
        $this->createResolverMock();
        $this->object->startTransaction($this);
        $this->object->expects(
            $this->once()
        )->method(
            '_applyOneFixture'
        )->with(
            [__CLASS__, 'sampleFixtureTwoRollback']
        );
        $this->object->rollbackTransaction();
    }

    /**
     * @magentoDataFixture path/to/fixture/script.php
     * @magentoDataFixture Magento/Test/Annotation/_files/sample_fixture_two.php
     *
     * @return void
     */
    public function testRollbackTransactionRevertFixtureFile(): void
    {
        $this->createResolverMock();
        $this->object->startTransaction($this);
        $this->object->expects(
            $this->once()
        )->method(
            '_applyOneFixture'
        )->with(
            $this->stringEndsWith('sample_fixture_two_rollback.php')
        );
        $this->object->rollbackTransaction();
    }

    /**
     * @magentoDataFixture Foo_DataFixtureDummy::Test/Integration/foo.php
     *
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testModuleDataFixture(): void
    {
        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Foo_DataFixtureDummy',
            __DIR__
        );
        $this->createResolverMock();
        $this->object->expects($this->once())->method('_applyOneFixture')
            ->with(__DIR__ . '/Test/Integration/foo.php');
        $this->object->startTransaction($this);
    }

    /**
     * Create mock for Resolver object
     *
     * @return void
     * @throws \ReflectionException
     */
    private function createResolverMock(): void
    {
        $mock = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['applyDataFixtures', 'getComponentRegistrar'])
            ->getMock();
        $mock->expects($this->any())->method('getComponentRegistrar')
            ->willReturn(new ComponentRegistrar());
        $reflection = new \ReflectionClass(Resolver::class);
        $reflectionProperty = $reflection->getProperty('instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(Resolver::class, $mock);
        $reflectionMethod = $reflection->getMethod('processFixturePath');
        $reflectionMethod->setAccessible(true);
        $annotatedFixtures = $this->getFixturesAnnotations();
        $resolvedFixtures = [];
        foreach ($annotatedFixtures as $fixture) {
            $resolvedFixtures[] = $reflectionMethod->invoke($mock, $this, $fixture);
        }
        $mock->method('applyDataFixtures')
            ->willReturn($resolvedFixtures);
    }

    /**
     * Prepare mock method return value
     *
     * @return array
     */
    private function getFixturesAnnotations(): array
    {
        $reflection = new \ReflectionClass(DataFixture::class);
        $reflectionMethod = $reflection->getMethod('getAnnotations');
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($this->object, $this)['magentoDataFixture'];
    }
}
