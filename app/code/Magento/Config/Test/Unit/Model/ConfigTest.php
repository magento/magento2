<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model;

use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Loader;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Reader;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\DB\Transaction;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Store\Model\ScopeTypeNormalizer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $model;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var Reader|MockObject
     */
    private $structureReaderMock;

    /**
     * @var TransactionFactory|MockObject
     */
    private $transFactoryMock;

    /**
     * @var ReinitableConfigInterface|MockObject
     */
    private $appConfigMock;

    /**
     * @var Loader|MockObject
     */
    private $configLoaderMock;

    /**
     * @var ValueFactory|MockObject
     */
    private $dataFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Structure|MockObject
     */
    private $configStructure;

    /**
     * @var SettingChecker|MockObject
     */
    private $settingsChecker;

    /**
     * @var ScopeResolverPool|MockObject
     */
    private $scopeResolverPool;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scope;

    /**
     * @var ScopeTypeNormalizer|MockObject
     */
    private $scopeTypeNormalizer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->structureReaderMock = $this->getMockBuilder(Reader::class)
            ->addMethods(['getConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configStructure = $this->createMock(Structure::class);

        $this->structureReaderMock->expects(
            $this->any()
        )->method(
            'getConfiguration'
        )->willReturn(
            $this->configStructure
        );

        $this->transFactoryMock = $this->getMockBuilder(TransactionFactory::class)
            ->addMethods(['addObject'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->appConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);
        $this->configLoaderMock = $this->createPartialMock(
            Loader::class,
            ['getConfigByPath']
        );
        $this->dataFactoryMock = $this->createMock(ValueFactory::class);

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->settingsChecker = $this
            ->createMock(SettingChecker::class);

        $this->scopeResolverPool = $this->createMock(ScopeResolverPool::class);
        $this->scopeResolver = $this->getMockForAbstractClass(ScopeResolverInterface::class);
        $this->scopeResolverPool->method('get')
            ->willReturn($this->scopeResolver);
        $this->scope = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->scopeResolver->method('getScope')
            ->willReturn($this->scope);

        $this->scopeTypeNormalizer = $this->createMock(ScopeTypeNormalizer::class);

        $stubPillPut = $this->getMockForAbstractClass(PoisonPillPutInterface::class);

        $this->model = new Config(
            $this->appConfigMock,
            $this->eventManagerMock,
            $this->configStructure,
            $this->transFactoryMock,
            $this->configLoaderMock,
            $this->dataFactoryMock,
            $this->storeManager,
            $this->settingsChecker,
            [],
            $this->scopeResolverPool,
            $this->scopeTypeNormalizer,
            $stubPillPut
        );
    }

    /**
     * @return void
     */
    public function testSaveDoesNotDoAnythingIfGroupsAreNotPassed(): void
    {
        $this->appConfigMock->expects($this->never())
            ->method('reinit');
        $this->configLoaderMock->expects($this->never())->method('getConfigByPath');
        $this->model->save();
    }

    /**
     * @return void
     */
    public function testSaveEmptiesNonSetArguments(): void
    {
        $this->appConfigMock->expects($this->never())
            ->method('reinit');
        $this->structureReaderMock->expects($this->never())->method('getConfiguration');
        $this->assertNull($this->model->getSection());
        $this->assertNull($this->model->getWebsite());
        $this->assertNull($this->model->getStore());
        $this->model->save();
        $this->assertSame('', $this->model->getSection());
        $this->assertSame('', $this->model->getWebsite());
        $this->assertSame('', $this->model->getStore());
    }

    /**
     * @return void
     */
    public function testSaveToCheckAdminSystemConfigChangedSectionEvent(): void
    {
        $this->appConfigMock->expects($this->exactly(2))
            ->method('reinit');
        $transactionMock = $this->createMock(Transaction::class);

        $this->transFactoryMock->expects($this->any())->method('create')->willReturn($transactionMock);

        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->willReturn([]);

        $this->eventManagerMock
            ->method('dispatch')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1== 'admin_system_config_changed_section_' &&
                    (array_key_exists('website', $arg2) || array_key_exists('store', $arg2))) {
                    return null;
                }
            });

        $this->model->setGroups(['1' => ['data']]);
        $this->model->save();
    }

    /**
     * @return void
     */
    public function testDoNotSaveReadOnlyFields(): void
    {
        $this->appConfigMock->expects($this->exactly(2))
            ->method('reinit');
        $transactionMock = $this->createMock(Transaction::class);
        $this->transFactoryMock->expects($this->any())->method('create')->willReturn($transactionMock);

        $this->settingsChecker->expects($this->any())->method('isReadOnly')->willReturn(true);
        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->willReturn([]);

        $this->model->setGroups(['1' => ['fields' => ['key' => ['data']]]]);
        $this->model->setSection('section');

        $group = $this->createMock(Group::class);
        $group->method('getPath')->willReturn('section/1');

        $field = $this->createMock(Field::class);
        $field->method('getGroupPath')->willReturn('section/1');
        $field->method('getId')->willReturn('key');

        $this->configStructure
            ->method('getElement')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['section/1'] => $group,
                ['section/1/key'] => $field
            });

        $backendModel = $this->createPartialMock(
            Value::class,
            ['addData']
        );
        $this->dataFactoryMock->expects($this->any())->method('create')->willReturn($backendModel);

        $this->transFactoryMock->expects($this->never())->method('addObject');
        $backendModel->expects($this->never())->method('addData');

        $this->model->save();
    }

    /**
     * @return void
     */
    public function testSaveToCheckScopeDataSet(): void
    {
        $this->appConfigMock->expects($this->exactly(2))
            ->method('reinit');
        $transactionMock = $this->createMock(Transaction::class);
        $this->transFactoryMock->expects($this->any())->method('create')->willReturn($transactionMock);

        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->willReturn([]);

        $this->eventManagerMock
            ->method('dispatch')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1== 'admin_system_config_changed_section_' &&
                    (array_key_exists('website', $arg2) || array_key_exists('store', $arg2))) {
                    return null;
                }
            });

        $group = $this->createMock(Group::class);
        $group->method('getPath')->willReturn('section/1');

        $field = $this->createMock(Field::class);
        $field->method('getGroupPath')->willReturn('section/1');
        $field->method('getId')->willReturn('key');

        $this->configStructure
            ->method('getElement')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['section/1'] => $group,
                ['section/1/key'] => $field
            });

        $this->scopeResolver->expects($this->atLeastOnce())
            ->method('getScope')
            ->with('1')
            ->willReturn($this->scope);
        $this->scope->expects($this->atLeastOnce())
            ->method('getScopeType')
            ->willReturn('website');
        $this->scope->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->scope->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('website_code');
        $this->scopeTypeNormalizer->expects($this->atLeastOnce())
            ->method('normalize')
            ->with('website')
            ->willReturn('websites');
        $website = $this->createMock(Website::class);
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([$website]);
        $this->storeManager->expects($this->any())->method('isSingleStoreMode')->willReturn(true);

        $this->model->setWebsite('1');
        $this->model->setSection('section');
        $this->model->setGroups(['1' => ['fields' => ['key' => ['data']]]]);

        $backendModel = $this->getMockBuilder(Value::class)
            ->addMethods(['setPath'])
            ->onlyMethods(['addData', '__sleep', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $backendModel->expects($this->once())
            ->method('addData')
            ->with([
                'field' => 'key',
                'groups' => [1 => ['fields' => ['key' => ['data']]]],
                'group_id' => null,
                'scope' => 'websites',
                'scope_id' => 1,
                'scope_code' => 'website_code',
                'field_config' => null,
                'fieldset_data' => ['key' => null]
            ]);
        $backendModel->expects($this->once())
            ->method('setPath')
            ->with('section/1/key')
            ->willReturn($backendModel);

        $this->dataFactoryMock->expects($this->any())->method('create')->willReturn($backendModel);

        $this->model->save();
    }

    /**
     * @param string $path
     * @param string $value
     * @param string $section
     * @param array $groups
     *
     * @return void
     * @dataProvider setDataByPathDataProvider
     */
    public function testSetDataByPath(string $path, string $value, string $section, array $groups): void
    {
        $this->model->setDataByPath($path, $value);
        $this->assertEquals($section, $this->model->getData('section'));
        $this->assertEquals($groups, $this->model->getData('groups'));
    }

    /**
     * @return array
     */
    public static function setDataByPathDataProvider(): array
    {
        return [
            'depth 3' => [
                'a/b/c',
                'value1',
                'a',
                [
                    'b' => [
                        'fields' => [
                            'c' => ['value' => 'value1']
                        ],
                    ],
                ],
            ],
            'depth 5' => [
                'a/b/c/d/e',
                'value1',
                'a',
                [
                    'b' => [
                        'groups' => [
                            'c' => [
                                'groups' => [
                                    'd' => [
                                        'fields' => [
                                            'e' => ['value' => 'value1']
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    public function testSetDataByPathEmpty(): void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Path must not be empty');
        $this->model->setDataByPath('', 'value');
    }

    /**
     * @param string $path
     *
     * @return void
     * @dataProvider setDataByPathWrongDepthDataProvider
     */
    public function testSetDataByPathWrongDepth(string $path): void
    {
        $currentDepth = count(explode('/', $path));
        $expectedException = 'Minimal depth of configuration is 3. Your configuration depth is ' . $currentDepth;
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage($expectedException);
        $value = 'value';
        $this->model->setDataByPath($path, $value);
    }

    /**
     * @return array
     */
    public static function setDataByPathWrongDepthDataProvider(): array
    {
        return [
            'depth 2' => ['section/group'],
            'depth 1' => ['section']
        ];
    }
}
