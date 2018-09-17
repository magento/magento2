<?php
/**
 * RouterList model test class
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Router;

class ActionListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Config\CacheInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Framework\Module\Dir\Reader | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var \Magento\Framework\App\Router\ActionList
     */
    protected $actionList;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheMock = $this->getMockBuilder('Magento\Framework\Config\CacheInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleReaderMock = $this->getMockBuilder('Magento\Framework\Module\Dir\Reader')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructorCachedData()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(serialize('data')));
        $this->cacheMock->expects($this->never())
            ->method('save');
        $this->moduleReaderMock->expects($this->never())
            ->method('getActionFiles');
        $this->actionList = $this->objectManager->getObject(
            'Magento\Framework\App\Router\ActionList',
            [
                'cache' => $this->cacheMock,
                'moduleReader' => $this->moduleReaderMock,
            ]
        );
    }

    public function testConstructorNoCachedData()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->cacheMock->expects($this->once())
            ->method('save');
        $this->moduleReaderMock->expects($this->once())
            ->method('getActionFiles')
            ->will($this->returnValue('data'));
        $this->actionList = $this->objectManager->getObject(
            'Magento\Framework\App\Router\ActionList',
            [
                'cache' => $this->cacheMock,
                'moduleReader' => $this->moduleReaderMock,
            ]
        );
    }

    /**
     * @param string $module
     * @param string $area
     * @param string $namespace
     * @param string $action
     * @param array $data
     * @param string|null $expected
     * @dataProvider getDataProvider
     */
    public function testGet($module, $area, $namespace, $action, $data, $expected)
    {

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->cacheMock->expects($this->once())
            ->method('save');
        $this->moduleReaderMock->expects($this->once())
            ->method('getActionFiles')
            ->will($this->returnValue($data));
        $this->actionList = $this->objectManager->getObject(
            'Magento\Framework\App\Router\ActionList',
            [
                'cache' => $this->cacheMock,
                'moduleReader' => $this->moduleReaderMock,
            ]
        );
        $this->assertEquals($expected, $this->actionList->get($module, $area, $namespace, $action));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        $mockClassName = 'Mock_Action_Class';
        $actionClass = $this->getMockClass(
            'Magento\Framework\App\ActionInterface',
            ['execute', 'getResponse'],
            [],
            $mockClassName
        );

        return [
            [
                'Magento_Module',
                'Area',
                'Namespace',
                'Index',
                ['magento\module\controller\area\namespace\index' => $mockClassName],
                $actionClass
            ],
            [
                'Magento_Module',
                '',
                'Namespace',
                'Index',
                ['magento\module\controller\namespace\index' => $mockClassName],
                $actionClass
            ],
            [
                'Magento_Module',
                'Area',
                'Namespace',
                'Catch',
                ['magento\module\controller\area\namespace\catchaction' => $mockClassName],
                $actionClass
            ],
            [
                'Magento_Module',
                'Area',
                'Namespace',
                'Index',
                ['magento\module\controller\area\namespace\index' => 'Not_Exist_Class'],
                null
            ],
            [
                'Magento_Module',
                'Area',
                'Namespace',
                'Index',
                [],
                null
            ],
            [
                'Magento_Module',
                null,
                'adminhtml_product',
                'index',
                'magento\module\controller\adminhtml\product\index' => '$mockClassName',
                null
            ],
        ];
    }
}
