<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Config\Model\Config\Structure\Reader;
use Magento\Framework\DB\TransactionFactory;
use Magento\Config\Model\Config\Loader;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\ScopeTypeNormalizer;
use Magento\Framework\DB\Transaction;
use Magento\Framework\App\Config\Value;
use Magento\Store\Model\Website;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Field;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->structureReaderMock = $this->createPartialMock(
            Reader::class,
            ['getConfiguration']
        );
        $this->configStructure = $this->createMock(Structure::class);

        $this->structureReaderMock->method('getConfiguration')
            ->willReturn($this->configStructure);

        $this->transFactoryMock = $this->createPartialMock(
            TransactionFactory::class,
            ['create', 'addObject']
        );
        $this->appConfigMock = $this->createMock(ReinitableConfigInterface::class);
        $this->configLoaderMock = $this->createPartialMock(
            Loader::class,
            ['getConfigByPath']
        );
        $this->dataFactoryMock = $this->createMock(ValueFactory::class);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $this->settingsChecker = $this->createMock(SettingChecker::class);

        $this->scopeResolverPool = $this->createMock(ScopeResolverPool::class);
        $this->scopeResolver = $this->createMock(ScopeResolverInterface::class);
        $this->scopeResolverPool->method('get')
            ->willReturn($this->scopeResolver);
        $this->scope = $this->createMock(ScopeInterface::class);
        $this->scopeResolver->method('getScope')
            ->willReturn($this->scope);

        $this->scopeTypeNormalizer = $this->createMock(ScopeTypeNormalizer::class);

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
            $this->scopeTypeNormalizer
        );
    }

    public function testSaveDoesNotDoAnythingIfGroupsAreNotPassed()
    {
        $this->configLoaderMock->expects($this->never())->method('getConfigByPath');
        $this->model->save();
    }

    public function testSaveEmptiesNonSetArguments()
    {
        $this->structureReaderMock->expects($this->never())->method('getConfiguration');
        $this->assertNull($this->model->getSection());
        $this->assertNull($this->model->getWebsite());
        $this->assertNull($this->model->getStore());
        $this->model->save();
        $this->assertSame('', $this->model->getSection());
        $this->assertSame('', $this->model->getWebsite());
        $this->assertSame('', $this->model->getStore());
    }

    public function testSaveToCheckAdminSystemConfigChangedSectionEvent()
    {
        $transactionMock = $this->createMock(Transaction::class);

        $this->transFactoryMock->method('create')
            ->willReturn($transactionMock);

        $this->configLoaderMock->method('getConfigByPath')
            ->willReturn([]);

        $this->eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            $this->equalTo('admin_system_config_changed_section_'),
            $this->arrayHasKey('website')
        );

        $this->eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            $this->equalTo('admin_system_config_changed_section_'),
            $this->arrayHasKey('store')
        );

        $this->model->setGroups(['1' => ['data']]);
        $this->model->save();
    }

    public function testDoNotSaveReadOnlyFields()
    {
        $transactionMock = $this->createMock(Transaction::class);
        $this->transFactoryMock->method('create')
            ->willReturn($transactionMock);

        $this->settingsChecker->method('isReadOnly')
            ->willReturn(true);
        $this->configLoaderMock->method('getConfigByPath')
            ->willReturn([]);

        $this->model->setGroups(['1' => ['fields' => ['key' => ['data']]]]);
        $this->model->setSection('section');

        $group = $this->createMock(Group::class);
        $group->method('getPath')
            ->willReturn('section/1');

        $field = $this->createMock(Field::class);
        $field->method('getGroupPath')
            ->willReturn('section/1');
        $field->method('getId')
            ->willReturn('key');

        $this->configStructure->expects($this->at(0))
            ->method('getElement')
            ->with('section/1')
            ->willReturn($group);
        $this->configStructure->expects($this->at(1))
            ->method('getElement')
            ->with('section/1')
            ->willReturn($group);
        $this->configStructure->expects($this->at(2))
            ->method('getElement')
            ->with('section/1/key')
            ->willReturn($field);

        $backendModel = $this->createPartialMock(
            Value::class,
            ['addData']
        );
        $this->dataFactoryMock->method('create')
            ->willReturn($backendModel);

        $this->transFactoryMock->expects($this->never())
            ->method('addObject');
        $backendModel->expects($this->never())
            ->method('addData');

        $this->model->save();
    }

    public function testSaveToCheckScopeDataSet()
    {
        $transactionMock = $this->createMock(Transaction::class);
        $this->transFactoryMock->method('create')
            ->willReturn($transactionMock);

        $this->configLoaderMock->method('getConfigByPath')
            ->willReturn([]);

        $this->eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo('admin_system_config_changed_section_section'),
                $this->arrayHasKey('website')
            );
        $this->eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo('admin_system_config_changed_section_section'),
                $this->arrayHasKey('store')
            );

        $group = $this->createMock(Group::class);
        $group->method('getPath')->willReturn('section/1');

        $field = $this->createMock(Field::class);
        $field->method('getGroupPath')->willReturn('section/1');
        $field->method('getId')->willReturn('key');

        $this->configStructure->expects($this->at(0))
            ->method('getElement')
            ->with('section/1')
            ->willReturn($group);
        $this->configStructure->expects($this->at(1))
            ->method('getElement')
            ->with('section/1')
            ->willReturn($group);
        $this->configStructure->expects($this->at(2))
            ->method('getElement')
            ->with('section/1/key')
            ->willReturn($field);
        $this->configStructure->expects($this->at(3))
            ->method('getElement')
            ->with('section/1')
            ->willReturn($group);
        $this->configStructure->expects($this->at(4))
            ->method('getElement')
            ->with('section/1/key')
            ->willReturn($field);

        $this->scopeResolver->method('getScope')
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
        $this->storeManager->method('getWebsites')->willReturn([$website]);
        $this->storeManager->method('isSingleStoreMode')->willReturn(true);

        $this->model->setWebsite('1');
        $this->model->setSection('section');
        $this->model->setGroups(['1' => ['fields' => ['key' => ['data']]]]);

        $backendModel = $this->createPartialMock(
            Value::class,
            ['setPath', 'addData', '__sleep', '__wakeup']
        );
        $backendModel->method('addData')
            ->with([
                'field' => 'key',
                'groups' => [1 => ['fields' => ['key' => ['data']]]],
                'group_id' => null,
                'scope' => 'websites',
                'scope_id' => 1,
                'scope_code' => 'website_code',
                'field_config' => null,
                'fieldset_data' => ['key' => null],
            ]);
        $backendModel->expects($this->once())
            ->method('setPath')
            ->with('section/1/key')
            ->willReturn($backendModel);

        $this->dataFactoryMock->method('create')
            ->willReturn($backendModel);

        $this->model->save();
    }

    /**
     * @param string $path
     * @param string $value
     * @param string $section
     * @param array $groups
     * @return void
     * @dataProvider setDataByPathDataProvider
     */
    public function testSetDataByPath(string $path, string $value, string $section, array $groups)
    {
        $this->model->setDataByPath($path, $value);
        $this->assertEquals($section, $this->model->getData('section'));
        $this->assertEquals($groups, $this->model->getData('groups'));
    }

    /**
     * @return array
     */
    public function setDataByPathDataProvider(): array
    {
        return [
            'depth 3' => [
                'a/b/c',
                'value1',
                'a',
                [
                    'b' => [
                        'fields' => [
                            'c' => ['value' => 'value1'],
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
                                            'e' => ['value' => 'value1'],
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
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Path must not be empty
     */
    public function testSetDataByPathEmpty()
    {
        $this->model->setDataByPath('', 'value');
    }

    /**
     * @param string $path
     * @return void
     * @dataProvider setDataByPathWrongDepthDataProvider
     */
    public function testSetDataByPathWrongDepth(string $path)
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
    public function setDataByPathWrongDepthDataProvider(): array
    {
        return [
            'depth 2' => ['section/group'],
            'depth 1' => ['section'],
        ];
    }
}
