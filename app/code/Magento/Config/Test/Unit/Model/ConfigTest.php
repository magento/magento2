<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_structureReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_configStructure;

    protected function setUp()
    {
        $this->_eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_structureReaderMock = $this->createPartialMock(
            \Magento\Config\Model\Config\Structure\Reader::class,
            ['getConfiguration']
        );
        $this->_configStructure = $this->createMock(\Magento\Config\Model\Config\Structure::class);

        $this->_structureReaderMock->expects(
            $this->any()
        )->method(
            'getConfiguration'
        )->will(
            $this->returnValue($this->_configStructure)
        );

        $this->_transFactoryMock = $this->createPartialMock(
            \Magento\Framework\DB\TransactionFactory::class,
            ['create']
        );
        $this->_appConfigMock = $this->createMock(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $this->_configLoaderMock = $this->createPartialMock(
            \Magento\Config\Model\Config\Loader::class,
            ['getConfigByPath']
        );
        $this->_dataFactoryMock = $this->createMock(\Magento\Framework\App\Config\ValueFactory::class);

        $this->_storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);

        $this->_model = new \Magento\Config\Model\Config(
            $this->_appConfigMock,
            $this->_eventManagerMock,
            $this->_configStructure,
            $this->_transFactoryMock,
            $this->_configLoaderMock,
            $this->_dataFactoryMock,
            $this->_storeManager
        );
    }

    public function testSaveDoesNotDoAnythingIfGroupsAreNotPassed()
    {
        $this->_configLoaderMock->expects($this->never())->method('getConfigByPath');
        $this->_model->save();
    }

    public function testSaveEmptiesNonSetArguments()
    {
        $this->_structureReaderMock->expects($this->never())->method('getConfiguration');
        $this->assertNull($this->_model->getSection());
        $this->assertNull($this->_model->getWebsite());
        $this->assertNull($this->_model->getStore());
        $this->_model->save();
        $this->assertSame('', $this->_model->getSection());
        $this->assertSame('', $this->_model->getWebsite());
        $this->assertSame('', $this->_model->getStore());
    }

    public function testSaveToCheckAdminSystemConfigChangedSectionEvent()
    {
        $transactionMock = $this->createMock(\Magento\Framework\DB\Transaction::class);

        $this->_transFactoryMock->expects($this->any())->method('create')->will($this->returnValue($transactionMock));

        $this->_configLoaderMock->expects($this->any())->method('getConfigByPath')->will($this->returnValue([]));

        $this->_eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            $this->equalTo('admin_system_config_changed_section_'),
            $this->arrayHasKey('website')
        );

        $this->_eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            $this->equalTo('admin_system_config_changed_section_'),
            $this->arrayHasKey('store')
        );

        $this->_model->setGroups(['1' => ['data']]);
        $this->_model->save();
    }

    public function testSaveToCheckScopeDataSet()
    {
        $transactionMock = $this->createMock(\Magento\Framework\DB\Transaction::class);

        $this->_transFactoryMock->expects($this->any())->method('create')->will($this->returnValue($transactionMock));

        $this->_configLoaderMock->expects($this->any())->method('getConfigByPath')->will($this->returnValue([]));

        $this->_eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            $this->equalTo('admin_system_config_changed_section_'),
            $this->arrayHasKey('website')
        );

        $this->_eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            $this->equalTo('admin_system_config_changed_section_'),
            $this->arrayHasKey('store')
        );

        $group = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);

        $field = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Field::class);

        $this->_configStructure->expects(
            $this->at(0)
        )->method(
            'getElement'
        )->with(
            '/1'
        )->will(
            $this->returnValue($group)
        );

        $this->_configStructure->expects(
            $this->at(1)
        )->method(
            'getElement'
        )->with(
            '/1/key'
        )->will(
            $this->returnValue($field)
        );

        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $website->expects($this->any())->method('getCode')->will($this->returnValue('website_code'));
        $this->_storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($website));
        $this->_storeManager->expects($this->any())->method('getWebsites')->will($this->returnValue([$website]));
        $this->_storeManager->expects($this->any())->method('isSingleStoreMode')->will($this->returnValue(true));

        $this->_model->setWebsite('website');

        $this->_model->setGroups(['1' => ['fields' => ['key' => ['data']]]]);

        $backendModel = $this->createPartialMock(
            \Magento\Framework\App\Config\Value::class,
            ['setPath', 'addData', '__sleep', '__wakeup']
        );
        $backendModel->expects(
            $this->once()
        )->method(
            'addData'
        )->with(
            [
                'field' => 'key',
                'groups' => [1 => ['fields' => ['key' => ['data']]]],
                'group_id' => null,
                'scope' => 'websites',
                'scope_id' => 0,
                'scope_code' => 'website_code',
                'field_config' => null,
                'fieldset_data' => ['key' => null],
            ]
        );
        $backendModel->expects(
            $this->once()
        )->method(
            'setPath'
        )->with(
            '/key'
        )->will(
            $this->returnValue($backendModel)
        );

        $this->_dataFactoryMock->expects($this->any())->method('create')->will($this->returnValue($backendModel));

        $this->_model->save();
    }

    public function testSetDataByPath()
    {
        $value = 'value';
        $path = '<section>/<group>/<field>';
        $this->_model->setDataByPath($path, $value);
        $expected = [
            'section' => '<section>',
            'groups' => [
                '<group>' => [
                    'fields' => [
                        '<field>' => ['value' => $value],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $this->_model->getData());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Path must not be empty
     */
    public function testSetDataByPathEmpty()
    {
        $this->_model->setDataByPath('', 'value');
    }

    /**
     * @param string $path
     * @param string $expectedException
     *
     * @dataProvider setDataByPathWrongDepthDataProvider
     */
    public function testSetDataByPathWrongDepth($path, $expectedException)
    {
        $expectedException = 'Allowed depth of configuration is 3 (<section>/<group>/<field>). ' . $expectedException;
        $this->expectException('\UnexpectedValueException', $expectedException);
        $value = 'value';
        $this->_model->setDataByPath($path, $value);
    }

    /**
     * @return array
     */
    public function setDataByPathWrongDepthDataProvider()
    {
        return [
            'depth 2' => ['section/group', "Your configuration depth is 2 for path 'section/group'"],
            'depth 1' => ['section', "Your configuration depth is 1 for path 'section'"],
            'depth 4' => ['section/group/field/sub-field', "Your configuration depth is 4 for path"
            . " 'section/group/field/sub-field'", ],
        ];
    }
}
