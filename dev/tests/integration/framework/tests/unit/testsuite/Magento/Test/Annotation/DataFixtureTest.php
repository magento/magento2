<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Annotation;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Annotation\DataFixture as DataFixtureAnnotation;
use Magento\TestFramework\Annotation\DataFixtureSetup;
use Magento\TestFramework\Event\Param\Transaction;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Fixture\LegacyDataFixturePathResolver;
use Magento\TestFramework\Fixture\DataFixtureFactory;
use Magento\TestFramework\Fixture\LegacyDataFixture;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Annotation\TestsIsolation;
use ReflectionException;

/**
 * Test class for \Magento\TestFramework\Annotation\DataFixture.
 *
 * @magentoDataFixture sampleFixtureOne
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataFixtureTest extends TestCase
{
    /**
     * @var DataFixtureAnnotation|MockObject
     */
    protected $object;

    /**
     * @var TestsIsolation|MockObject
     */
    protected $testsIsolationMock;

    /**
     * @var RevertibleDataFixtureInterface|MockObject
     */
    private $fixture1;

    /**
     * @var RevertibleDataFixtureInterface|MockObject
     */
    private $fixture2;

    /**
     * @var DataFixtureInterface|MockObject
     */
    private $fixture3;

    /**
     * @var DataObject
     */
    private $fixtureStorage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->object = new DataFixtureAnnotation();
        $this->testsIsolationMock = $this->getMockBuilder(TestsIsolation::class)
            ->onlyMethods(['createDbSnapshot', 'checkTestIsolation'])
            ->getMock();
        /** @var ObjectManagerInterface|MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['get', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fixture1 = $this->getMockBuilder(RevertibleDataFixtureInterface::class)
            ->setMockClassName('MockFixture1')
            ->getMockForAbstractClass();
        $this->fixture2 = $this->getMockBuilder(RevertibleDataFixtureInterface::class)
            ->setMockClassName('MockFixture2')
            ->getMockForAbstractClass();
        $this->fixture3 = $this->getMockBuilder(DataFixtureInterface::class)
            ->setMockClassName('MockFixture3')
            ->getMockForAbstractClass();

        $this->fixtureStorage = new DataFixtureStorage();
        DataFixtureStorageManager::setStorage($this->fixtureStorage);

        $dataFixtureFactory = new DataFixtureFactory($objectManager);
        $annotationParser = new \Magento\TestFramework\Annotation\Parser\DataFixture('magentoDataFixture');

        $sharedInstances = [
            TestsIsolation::class => $this->testsIsolationMock,
            \Magento\TestFramework\Annotation\Parser\DataFixture::class => $annotationParser,
            DataFixtureFactory::class => $dataFixtureFactory,
            DataFixtureSetup::class => new DataFixtureSetup(new Registry(), $dataFixtureFactory),
            'MockFixture1' => $this->fixture1,
            'MockFixture2' => $this->fixture2,
            'MockFixture3' => $this->fixture3,
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
                function (string $type, array $arguments = []) use ($sharedInstances) {
                    if ($type === LegacyDataFixture::class) {
                        array_unshift($arguments, new LegacyDataFixturePathResolver(new ComponentRegistrar()));
                    }
                    return $sharedInstances[$type] ?? new $type(...array_values($arguments));
                }
            );
        Bootstrap::setObjectManager($objectManager);

        $directory = __DIR__;
        if (!defined('INTEGRATION_TESTS_DIR')) {
            define('INTEGRATION_TESTS_DIR', dirname($directory, 4));
        }

        $this->createResolverMock();
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
        $this->object->startTransaction($this);
        $this->assertEquals('3', getenv('sample_fixture_three'));
    }

    #[
        DbIsolation(false),
        DataFixture('MockFixture1'),
        DataFixture('MockFixture2'),
        DataFixture('MockFixture3'),
    ]
    public function testFixtureClass(): void
    {
        $fixture1 = new DataObject();
        $fixture2 = new DataObject();
        $this->fixture1->expects($this->once())
            ->method('apply')
            ->with([])
            ->willReturn($fixture1);
        $this->fixture2->expects($this->once())
            ->method('apply')
            ->with([])
            ->willReturn($fixture2);
        $this->fixture3->expects($this->once())
            ->method('apply')
            ->with([]);
        $this->applyFixtures();
        $this->fixture1->expects($this->once())
            ->method('revert')
            ->with($fixture1);
        $this->fixture2->expects($this->once())
            ->method('revert')
            ->with($fixture2);
        $this->revertFixtures();
    }

    #[
        DbIsolation(false),
        DataFixture('MockFixture1', as: 'fixture1'),
        DataFixture('MockFixture2', as: 'fixture2'),
        DataFixture('MockFixture3', as: 'fixture3'),
    ]
    public function testFixtureClassWithAlias(): void
    {
        $fixture1 = new DataObject();
        $fixture2 = new DataObject();
        $this->fixture1->expects($this->once())
            ->method('apply')
            ->with([])
            ->willReturn($fixture1);
        $this->fixture2->expects($this->once())
            ->method('apply')
            ->with([])
            ->willReturn($fixture2);
        $this->fixture3->expects($this->once())
            ->method('apply')
            ->with([]);
        $this->applyFixtures();
        $this->assertSame($fixture1, $this->fixtureStorage->get('fixture1'));
        $this->assertSame($fixture2, $this->fixtureStorage->get('fixture2'));
        $this->assertNull($this->fixtureStorage->get('fixture3'));
        $this->fixture1->expects($this->once())
            ->method('revert')
            ->with($fixture1);
        $this->fixture2->expects($this->once())
            ->method('revert')
            ->with($fixture2);
        $this->revertFixtures();
    }

    #[
        DbIsolation(false),
        DataFixture('MockFixture1', ['key1' => 'value1']),
        DataFixture('MockFixture2', ['key2' => 'value2']),
        DataFixture('MockFixture3', ['key3' => 'value3']),
    ]
    public function testFixtureClassWithParameters(): void
    {
        $fixture1 = new DataObject();
        $fixture2 = new DataObject();
        $this->fixture1->expects($this->once())
            ->method('apply')
            ->with(['key1' => 'value1'])
            ->willReturn($fixture1);
        $this->fixture2->expects($this->once())
            ->method('apply')
            ->with(['key2' => 'value2'])
            ->willReturn($fixture2);
        $this->fixture3->expects($this->once())
            ->method('apply')
            ->with(['key3' => 'value3']);
        $this->applyFixtures();
        $this->fixture1->expects($this->once())
            ->method('revert')
            ->with($fixture1);
        $this->fixture2->expects($this->once())
            ->method('revert')
            ->with($fixture2);
        $this->revertFixtures();
    }

    #[
        DbIsolation(false),
        DataFixture('MockFixture1', ['alias-key1' => 'alias-value1'], 'fixture1'),
        DataFixture('MockFixture2', ['alias-key2' => 'alias-value2'], 'fixture2'),
        DataFixture('MockFixture3', ['alias-key3' => 'alias-value3'], 'fixture3'),
    ]
    public function testFixtureClassWithParametersAndAlias(): void
    {
        $fixture1 = new DataObject();
        $fixture2 = new DataObject();
        $this->fixture1->expects($this->once())
            ->method('apply')
            ->with(['alias-key1' => 'alias-value1'])
            ->willReturn($fixture1);
        $this->fixture2->expects($this->once())
            ->method('apply')
            ->with(['alias-key2' => 'alias-value2'])
            ->willReturn($fixture2);
        $this->fixture3->expects($this->once())
            ->method('apply')
            ->with(['alias-key3' => 'alias-value3']);
        $this->applyFixtures();
        $this->assertSame($fixture1, $this->fixtureStorage->get('fixture1'));
        $this->assertSame($fixture2, $this->fixtureStorage->get('fixture2'));
        $this->assertNull($this->fixtureStorage->get('fixture3'));
        $this->fixture1->expects($this->once())
            ->method('revert')
            ->with($fixture1);
        $this->fixture2->expects($this->once())
            ->method('revert')
            ->with($fixture2);
        $this->revertFixtures();
    }

    #[
        DbIsolation(false),
        DataFixture('MockFixture1', ['p1' => 'param-value1'], 'fixture1'),
        DataFixture('MockFixture2', ['p2' => '$fixture1.attr_1$'], 'fixture2'),
        DataFixture('MockFixture3', ['p3' => '$fixture2.attr_3$', 'p4' => ['p5' => '$fixture1$']], 'fixture3'),
    ]
    public function testVariables(): void
    {
        $fixture1 = new DataObject(['attr_1' => 'attr-value1', 'attr_2' => 'attr-value2']);
        $fixture2 = new DataObject(['attr_3' => 1]);
        $this->fixture1->expects($this->once())
            ->method('apply')
            ->with(['p1' => 'param-value1'])
            ->willReturn($fixture1);
        $this->fixture2->expects($this->once())
            ->method('apply')
            ->with(['p2' => 'attr-value1'])
            ->willReturn($fixture2);
        $this->fixture3->expects($this->once())
            ->method('apply')
            ->with(['p3' => 1, 'p4' => ['p5' => $fixture1]]);
        $this->applyFixtures();
        $this->assertSame($fixture1, $this->fixtureStorage->get('fixture1'));
        $this->assertSame($fixture2, $this->fixtureStorage->get('fixture2'));
        $this->assertNull($this->fixtureStorage->get('fixture3'));
        $this->fixture1->expects($this->once())
            ->method('revert')
            ->with($fixture1);
        $this->fixture2->expects($this->once())
            ->method('revert')
            ->with($fixture2);
        $this->revertFixtures();
    }

    /**
     * @throws ReflectionException
     */
    private function applyFixtures(): void
    {
        $this->object->startTransaction($this);
    }

    /**
     * @return void
     */
    private function revertFixtures(): void
    {
        $eventParam = new Transaction();
        $this->object->endTestTransactionRequest($this, $eventParam);
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
