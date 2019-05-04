<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config
     */
    private $model;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Config\Model\Config\Structure\Reader|MockObject
     */
    private $structureReaderMock;

    /**
     * @var \Magento\Framework\DB\TransactionFactory|MockObject
     */
    private $transFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface|MockObject
     */
    private $appConfigMock;

    /**
     * @var \Magento\Config\Model\Config\Loader|MockObject
     */
    private $configLoaderMock;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory|MockObject
     */
    private $dataFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Config\Model\Config\Structure|MockObject
     */
    private $configStructure;

    /**
     * @var \Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker|MockObject
     */
    private $settingsChecker;

    /**
     * @var \Magento\Framework\App\ScopeResolverPool|MockObject
     */
    private $scopeResolverPool;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var \Magento\Framework\App\ScopeInterface|MockObject
     */
    private $scope;

    /**
     * @var \Magento\Store\Model\ScopeTypeNormalizer|MockObject
     */
    private $scopeTypeNormalizer;

    protected function setUp()
    {
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->structureReaderMock = $this->createPartialMock(
            \Magento\Config\Model\Config\Structure\Reader::class,
            ['getConfiguration']
        );
        $this->configStructure = $this->createMock(\Magento\Config\Model\Config\Structure::class);

        $this->structureReaderMock->expects(
            $this->any()
        )->method(
            'getConfiguration'
        )->will(
            $this->returnValue($this->configStructure)
        );

        $this->transFactoryMock = $this->createPartialMock(
            \Magento\Framework\DB\TransactionFactory::class,
            ['create', 'addObject']
        );
        $this->appConfigMock = $this->createMock(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $this->configLoaderMock = $this->createPartialMock(
            \Magento\Config\Model\Config\Loader::class,
            ['getConfigByPath']
        );
        $this->dataFactoryMock = $this->createMock(\Magento\Framework\App\Config\ValueFactory::class);

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->settingsChecker = $this
            ->createMock(\Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker::class);

        $this->scopeResolverPool = $this->createMock(\Magento\Framework\App\ScopeResolverPool::class);
        $this->scopeResolver = $this->createMock(\Magento\Framework\App\ScopeResolverInterface::class);
        $this->scopeResolverPool->method('get')
            ->willReturn($this->scopeResolver);
        $this->scope = $this->createMock(\Magento\Framework\App\ScopeInterface::class);
        $this->scopeResolver->method('getScope')
            ->willReturn($this->scope);

        $this->scopeTypeNormalizer = $this->createMock(\Magento\Store\Model\ScopeTypeNormalizer::class);

        $this->model = new \Magento\Config\Model\Config(
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
        $transactionMock = $this->createMock(\Magento\Framework\DB\Transaction::class);

        $this->transFactoryMock->expects($this->any())->method('create')->will($this->returnValue($transactionMock));

        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->will($this->returnValue([]));

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
        $transactionMock = $this->createMock(\Magento\Framework\DB\Transaction::class);
        $this->transFactoryMock->expects($this->any())->method('create')->will($this->returnValue($transactionMock));

        $this->settingsChecker->expects($this->any())->method('isReadOnly')->will($this->returnValue(true));
        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->will($this->returnValue([]));

        $this->model->setGroups(['1' => ['fields' => ['key' => ['data']]]]);
        $this->model->setSection('section');

        $group = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);
        $group->method('getPath')->willReturn('section/1');

        $field = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Field::class);
        $field->method('getGroupPath')->willReturn('section/1');
        $field->method('getId')->willReturn('key');

        $this->configStructure->expects($this->at(0))
            ->method('getElement')
            ->with('section/1')
            ->will($this->returnValue($group));
        $this->configStructure->expects($this->at(1))
            ->method('getElement')
            ->with('section/1')
            ->will($this->returnValue($group));
        $this->configStructure->expects($this->at(2))
            ->method('getElement')
            ->with('section/1/key')
            ->will($this->returnValue($field));

        $backendModel = $this->createPartialMock(
            \Magento\Framework\App\Config\Value::class,
            ['addData']
        );
        $this->dataFactoryMock->expects($this->any())->method('create')->will($this->returnValue($backendModel));

        $this->transFactoryMock->expects($this->never())->method('addObject');
        $backendModel->expects($this->never())->method('addData');

        $this->model->save();
    }

    public function testSaveToCheckScopeDataSet()
    {
        $transactionMock = $this->createMock(\Magento\Framework\DB\Transaction::class);
        $this->transFactoryMock->expects($this->any())->method('create')->will($this->returnValue($transactionMock));

        $this->configLoaderMock->expects($this->any())->method('getConfigByPath')->will($this->returnValue([]));

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

        $group = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);
        $group->method('getPath')->willReturn('section/1');

        $field = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Field::class);
        $field->method('getGroupPath')->willReturn('section/1');
        $field->method('getId')->willReturn('key');

        $this->configStructure->expects($this->at(0))
            ->method('getElement')
            ->with('section/1')
            ->will($this->returnValue($group));
        $this->configStructure->expects($this->at(1))
            ->method('getElement')
            ->with('section/1')
            ->will($this->returnValue($group));
        $this->configStructure->expects($this->at(2))
            ->method('getElement')
            ->with('section/1/key')
            ->will($this->returnValue($field));
        $this->configStructure->expects($this->at(3))
            ->method('getElement')
            ->with('section/1')
            ->will($this->returnValue($group));
        $this->configStructure->expects($this->at(4))
            ->method('getElement')
            ->with('section/1/key')
            ->will($this->returnValue($field));

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
        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $this->storeManager->expects($this->any())->method('getWebsites')->will($this->returnValue([$website]));
        $this->storeManager->expects($this->any())->method('isSingleStoreMode')->will($this->returnValue(true));

        $this->model->setWebsite('1');
        $this->model->setSection('section');
        $this->model->setGroups(['1' => ['fields' => ['key' => ['data']]]]);

        $backendModel = $this->createPartialMock(
            \Magento\Framework\App\Config\Value::class,
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
                'fieldset_data' => ['key' => null],
            ]);
        $backendModel->expects($this->once())
            ->method('setPath')
            ->with('section/1/key')
            ->will($this->returnValue($backendModel));

        $this->dataFactoryMock->expects($this->any())->method('create')->will($this->returnValue($backendModel));

        $this->model->save();
    }

    /**
     * @param string $path
     * @param string $value
     * @param string $section
     * @param array $groups
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
