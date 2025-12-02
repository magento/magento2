<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\Role;

use Magento\Backend\Block\Widget\Tabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\User\Block\Role\Edit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class EditTest to cover Magento\User\Block\Role\Edit
 *
 */
class EditTest extends TestCase
{
    use MockCreationTrait;

    /** @var Edit|MockObject */
    protected $model;

    /** @var EncoderInterface|MockObject */
    protected $jsonEncoderMock;

    /** @var Session|MockObject */
    protected $authSessionsMock;

    /** @var Registry|MockObject */
    protected $registryMock;

    /** @var LayoutInterface|MockObject */
    protected $layoutInterfaceMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        
        $this->jsonEncoderMock = $this->createMock(EncoderInterface::class);
        $this->authSessionsMock = $this->createMock(Session::class);
        $this->registryMock = $this->createPartialMock(Registry::class, ['registry']);
        $this->layoutInterfaceMock = $this->createPartialMockWithReflection(
            LayoutInterface::class,
            [
                'getUpdate', 'generateXml', 'generateElements', 'renderElement', 'addOutputElement',
                'getOutput', 'hasElement', 'unsetElement', 'getAllBlocks', 'getBlock',
                'getChildBlock', 'setChild', 'reorderChild', 'unsetChild', 'getChildNames',
                'getChildBlocks', 'getChildName', 'addToParentGroup', 'getGroupChildNames',
                'getParentName', 'createBlock', 'addBlock', 'addContainer', 'renameElement',
                'getElementAlias', 'removeOutputElement', 'getMessagesBlock', 'getBlockSingleton',
                'getElementProperty', 'isBlock', 'isContainer', 'isManipulationAllowed',
                'setBlock', 'isCacheable', 'setRole', 'setActive', 'getId'
            ]
        );
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManagerHelper->prepareObjectManager($objects);
        $this->model = $objectManagerHelper->getObject(
            Edit::class,
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'authSession' => $this->authSessionsMock,
                'registry' => $this->registryMock,
                'layout' => $this->layoutInterfaceMock
            ]
        );
    }

    public function testPrepareLayoutSuccessOnFalseGetId()
    {
        $tab = 'tab';

        $this->registryMock->expects($this->once())->method('registry')->willReturn($this->layoutInterfaceMock);
        $this->layoutInterfaceMock->expects($this->any())->method('createBlock')->willReturnSelf();
        $this->layoutInterfaceMock->expects($this->once())->method('setRole')->willReturnSelf();
        $this->layoutInterfaceMock->expects($this->once())->method('setActive')->willReturn($tab);
        $this->layoutInterfaceMock->expects($this->once())->method('getId')->willReturn(false);

        $this->assertInstanceOf(
            Tabs::class,
            $this->invokeMethod($this->model, '_prepareLayout')
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed Method return.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
