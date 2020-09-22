<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Router;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionListTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var Reader|MockObject
     */
    private $readerMock;

    /**
     * @var ActionList
     */
    private $actionList;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->readerMock = $this->createMock(Reader::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
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
            ->willReturn('data');
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
            ActionInterface::class,
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
            ActionList::class,
            [
                'cache' => $this->cacheMock,
                'moduleReader' => $this->readerMock,
                'serializer' => $this->serializerMock,
            ]
        );
    }
}
