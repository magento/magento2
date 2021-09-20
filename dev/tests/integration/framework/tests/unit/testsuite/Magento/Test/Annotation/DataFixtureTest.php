<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Annotation;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Annotation\DataFixture;
use Magento\TestFramework\Event\Param\Transaction;
use Magento\TestFramework\Fixture\DataFixtureDirectivesParser;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\TestFramework\Fixture\DataFixtureSetup;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\LegacyDataFixturePathResolver;
use Magento\TestFramework\Fixture\DataFixtureFactory;
use Magento\TestFramework\Fixture\LegacyDataFixture;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Annotation\TestsIsolation;

/**
 * Test class for \Magento\TestFramework\Annotation\DataFixture.
 *
 * @magentoDataFixture sampleFixtureOne
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
            ->onlyMethods(['getTestKey'])
            ->addMethods(['getComponentRegistrar'])
            ->getMock();
        $this->testsIsolationMock = $this->getMockBuilder(TestsIsolation::class)
            ->onlyMethods(['createDbSnapshot', 'checkTestIsolation'])
            ->getMock();
        /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['get', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        DataFixtureStorageManager::setStorage(new DataFixtureStorage());
        $dataFixtureFactory = new DataFixtureFactory($objectManager);

        $sharedInstances = [
            TestsIsolation::class => $this->testsIsolationMock,
            DataFixtureDirectivesParser::class => new DataFixtureDirectivesParser(new Json()),
            DataFixtureFactory::class => $dataFixtureFactory,
            LegacyDataFixture::class => $this->createMock(DataFixtureInterface::class),
            DataFixtureSetup::class => new DataFixtureSetup($dataFixtureFactory),
        ];
        $objectManager->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(
                function (string $type) use ($sharedInstances) {
                    return $sharedInstances[$type] ?? new $type();
                }
            );
        $objectManager->expects($this->atLeastOnce())
            ->method('create')
            ->willReturnCallback(
                function (string $type, array $arguments = []) {
                    if ($type === LegacyDataFixture::class) {
                        array_unshift($arguments, new LegacyDataFixturePathResolver(new ComponentRegistrar()));
                    }
                    return new $type(...array_values($arguments));
                }
            );
        \Magento\TestFramework\Helper\Bootstrap::setObjectManager($objectManager);

        $directory = __DIR__;
        if (!defined('INTEGRATION_TESTS_DIR')) {
            define('INTEGRATION_TESTS_DIR', dirname($directory, 4));
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        putenv('sample_fixture_one');
        putenv('sample_fixture_two');
        putenv('sample_fixture_three');
    }

    /**
     * Dummy fixture
     *
     * @return void
     */
    public static function sampleFixtureOne(): void
    {
        putenv('sample_fixture_one=1');
    }

    /**
     * Dummy fixture
     *
     * @return void
     */
    public static function sampleFixtureTwo(): void
    {
        putenv('sample_fixture_two=2');
    }

    /**
     * Dummy fixture rollback
     *
     * @return void
     */
    public static function sampleFixtureTwoRollback(): void
    {
        putenv('sample_fixture_two');
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
     * @magentoDataFixture Magento/Test/Annotation/_files/sample_fixture_three.php
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
     * @magentoDataFixture Magento/Test/Annotation/_files/sample_fixture_three.php
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
     * @magentoDataFixture Magento/Test/Annotation/_files/sample_fixture_three.php
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
        $this->object->startTransaction($this);
        $this->assertEquals('1', getenv('sample_fixture_one'));
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture Magento/Test/Annotation/_files/sample_fixture_three.php
     *
     * @return void
     */
    public function testStartTransactionMethodAnnotation(): void
    {
        $this->createResolverMock();
        $this->object->startTransaction($this);
        $this->assertEquals('2', getenv('sample_fixture_two'));
        $this->assertEquals('3', getenv('sample_fixture_three'));
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
        $this->assertEquals('1', getenv('sample_fixture_one'));
        $this->assertEquals('2', getenv('sample_fixture_two'));
        $this->object->rollbackTransaction();
        $this->assertEquals('1', getenv('sample_fixture_one'));
        $this->assertFalse(getenv('sample_fixture_two'));
    }

    /**
     * @magentoDataFixture Magento/Test/Annotation/_files/sample_fixture_three.php
     *
     * @return void
     */
    public function testRollbackTransactionRevertFixtureFile(): void
    {
        $this->createResolverMock();
        $this->object->startTransaction($this);
        $this->assertEquals('3', getenv('sample_fixture_three'));
        $this->object->rollbackTransaction();
        $this->assertFalse(getenv('sample_fixture_three'));
    }

    /**
     * @magentoDataFixture Foo_DataFixtureDummy::Annotation/_files/sample_fixture_three.php
     *
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testModuleDataFixture(): void
    {
        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Foo_DataFixtureDummy',
            dirname(__DIR__)
        );
        $this->createResolverMock();
        $this->object->startTransaction($this);
        $this->assertEquals('3', getenv('sample_fixture_three'));
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
            ->onlyMethods(['applyDataFixtures'])
            ->getMock();
        $reflection = new \ReflectionClass(Resolver::class);
        $reflectionProperty = $reflection->getProperty('instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(Resolver::class, $mock);
        $mock->method('applyDataFixtures')
            ->willReturnArgument(1);
    }
}
