<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Router;

class ActionListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\App\Router\ActionList
     */
    private $actionList;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheMock = $this->createMock(\Magento\Framework\Config\CacheInterface::class);
        $this->readerMock = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
    }

    public function testConstructActionsCached()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('"data"');
        $this->serializerMock->expects($this->once())
            ->method('unserialize');
        $this->cacheMock->expects($this->never())
            ->method('save');
        $this->readerMock->expects($this->never())
            ->method('getActionFiles');
        $this->createActionListInstance();
    }

    public function testConstructActionsNoCached()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->serializerMock->expects($this->once())
            ->method('serialize');
        $this->cacheMock->expects($this->once())
            ->method('save');
        $this->readerMock->expects($this->once())
            ->method('getActionFiles')
            ->willReturn('data')
        ;
        $this->createActionListInstance();
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
            ->willReturn(false);
        $this->cacheMock->expects($this->once())
            ->method('save');
        $this->readerMock->expects($this->once())
            ->method('getActionFiles')
            ->willReturn($data);
        $this->createActionListInstance();
        $this->assertEquals($expected, $this->actionList->get($module, $area, $namespace, $action));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        $mockClassName = 'Mock_Action_Class';
        $actionClass = $this->getMockClass(
            \Magento\Framework\App\ActionInterface::class,
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

    private function createActionListInstance()
    {
        $this->actionList = $this->objectManager->getObject(
            \Magento\Framework\App\Router\ActionList::class,
            [
                'cache' => $this->cacheMock,
                'moduleReader' => $this->readerMock,
                'serializer' => $this->serializerMock,
            ]
        );
    }
}
