<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\ScopeTypeNormalizer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    use MockCreationTrait;

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
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->structureReaderMock = $this->createPartialMockWithReflection(
            Reader::class,
            ['getConfiguration']
        );
        $this->configStructure = $this->createMock(Structure::class);

        $this->structureReaderMock->expects(
            $this->any()
        )->method(
            'getConfiguration'
        )->willReturn(
            $this->configStructure
        );

        $this->transFactoryMock = $this->createPartialMockWithReflection(
            TransactionFactory::class,
            ['addObject', 'create']
        );
        $this->appConfigMock = $this->createMock(ReinitableConfigInterface::class);
        $this->configLoaderMock = $this->createPartialMock(
            Loader::class,
            ['getConfigByPath']
        );
        $this->dataFactoryMock = $this->createMock(ValueFactory::class);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $this->settingsChecker = $this
            ->createMock(SettingChecker::class);

        $this->scopeResolverPool = $this->createMock(ScopeResolverPool::class);
        $this->scopeResolver = $this->createMock(ScopeResolverInterface::class);
        $this->scopeResolverPool->method('get')
            ->willReturn($this->scopeResolver);
        $this->scope = $this->createMock(ScopeInterface::class);
        $this->scopeResolver->method('getScope')
            ->willReturn($this->scope);

        $this->scopeTypeNormalizer = $this->createMock(ScopeTypeNormalizer::class);

        $stubPillPut = $this->createMock(PoisonPillPutInterface::class);

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
     * When a dependent FieldArray is fully disabled, nothing for that field is posted.
     * Config must not touch backend models for fields absent from the groups payload.
     *
     * @return void
     */
    public function testSaveDoesNotTouchFieldsAbsentFromPostedGroups(): void
    {
        $this->appConfigMock->expects($this->exactly(2))->method('reinit');
        $transactionMock = $this->createMock(Transaction::class);
        $this->transFactoryMock->expects($this->any())->method('create')->willReturn($transactionMock);

        $this->settingsChecker->expects($this->any())->method('isReadOnly')->willReturn(false);

        // Existing stored FieldArray value must remain unless the field is in the POST
        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->willReturn([
            'section/general/items' => [
                'path' => 'section/general/items',
                'value' => '{"_row1":{"item_label":"keep-me"}}',
                'config_id' => 10,
            ],
            'section/general/enabled' => [
                'path' => 'section/general/enabled',
                'value' => '0',
                'config_id' => 11,
            ],
        ]);

        $group = $this->createMock(Group::class);
        $group->method('getPath')->willReturn('section/general');
        $group->method('getId')->willReturn('general');

        $enabledField = $this->createMock(Field::class);
        $enabledField->method('getGroupPath')->willReturn('section/general');
        $enabledField->method('getId')->willReturn('enabled');
        $enabledField->method('hasBackendModel')->willReturn(false);
        $enabledField->method('getType')->willReturn('select');
        $enabledField->method('getData')->willReturn([]);
        $enabledField->method('getConfigPath')->willReturn(null);

        $this->configStructure
            ->method('getElement')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['section/general'] => $group,
                ['section/general/enabled'] => $enabledField,
                default => $this->createMock(Field::class),
            });

        $createdPaths = [];
        $backendModel = $this->createPartialMockWithReflection(
            Value::class,
            ['addData', 'setPath', 'setValue', 'setConfigId', 'unsConfigId', '__sleep', '__wakeup']
        );
        $backendModel->method('setValue')->willReturnSelf();
        $backendModel->method('addData')->willReturnSelf();
        $backendModel->method('setConfigId')->willReturnSelf();
        $backendModel->expects($this->atLeastOnce())
            ->method('setPath')
            ->willReturnCallback(function ($path) use (&$createdPaths, $backendModel) {
                $createdPaths[] = $path;
                return $backendModel;
            });

        $this->dataFactoryMock->expects($this->any())->method('create')->willReturn($backendModel);

        // Only "enabled" is posted — "items" FieldArray is omitted (all inputs disabled)
        $this->model->setSection('section');
        $this->model->setGroups([
            'general' => [
                'fields' => [
                    'enabled' => ['value' => '0'],
                ],
            ],
        ]);
        $this->model->save();

        $this->assertNotContains(
            'section/general/items',
            $createdPaths,
            'FieldArray path must not be saved when the field is absent from the posted groups'
        );
        $this->assertContains('section/general/enabled', $createdPaths);
    }

    /**
     * When FieldArray posts only the __empty sentinel, Config still processes the field
     * and passes that payload to the backend model (which then serializes to []).
     *
     * @return void
     */
    public function testSavePassesEmptySentinelPayloadToFieldArrayBackend(): void
    {
        $this->appConfigMock->expects($this->exactly(2))->method('reinit');
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->atLeastOnce())->method('addObject');
        $this->transFactoryMock->expects($this->any())->method('create')->willReturn($transactionMock);

        $this->settingsChecker->expects($this->any())->method('isReadOnly')->willReturn(false);
        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->willReturn([
            'section/general/items' => [
                'path' => 'section/general/items',
                'value' => '{"_old":{"item_label":"x"}}',
                'config_id' => 10,
            ],
        ]);

        $group = $this->createMock(Group::class);
        $group->method('getPath')->willReturn('section/general');
        $group->method('getId')->willReturn('general');

        $itemsField = $this->createMock(Field::class);
        $itemsField->method('getGroupPath')->willReturn('section/general');
        $itemsField->method('getId')->willReturn('items');
        $itemsField->method('hasBackendModel')->willReturn(false);
        $itemsField->method('getType')->willReturn('text');
        $itemsField->method('getData')->willReturn([]);
        $itemsField->method('getConfigPath')->willReturn(null);

        $this->configStructure
            ->method('getElement')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['section/general'] => $group,
                ['section/general/items'] => $itemsField,
                default => $this->createMock(Field::class),
            });

        $postedValue = ['__empty' => ''];
        $backendModel = $this->createPartialMockWithReflection(
            Value::class,
            ['addData', 'setPath', 'setValue', 'setConfigId', 'unsConfigId', '__sleep', '__wakeup']
        );
        $backendModel->method('setPath')->willReturnSelf();
        $backendModel->method('addData')->willReturnSelf();
        $backendModel->expects($this->once())
            ->method('setValue')
            ->with($postedValue)
            ->willReturnSelf();
        $backendModel->expects($this->once())
            ->method('setConfigId')
            ->with(10)
            ->willReturnSelf();

        $this->dataFactoryMock->expects($this->any())->method('create')->willReturn($backendModel);

        $this->model->setSection('section');
        $this->model->setGroups([
            'general' => [
                'fields' => [
                    'items' => ['value' => $postedValue],
                ],
            ],
        ]);
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

        $backendModel = $this->createPartialMockWithReflection(
            Value::class,
            ['setPath', 'addData', '__sleep', '__wakeup']
        );
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
     */
    #[DataProvider('setDataByPathDataProvider')]
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
     */
    #[DataProvider('setDataByPathWrongDepthDataProvider')]
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
