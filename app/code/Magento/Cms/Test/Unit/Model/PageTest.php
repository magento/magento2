<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

/**
 * @covers \Magento\Cms\Model\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $thisMock;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourcePageMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $objectManager->getObject(
            'Magento\Framework\Model\Context',
            [
                'eventDispatcher' => $this->eventManagerMock
            ]
        );
        $this->resourcePageMock = $this->getMockBuilder('Magento\Cms\Model\ResourceModel\Page')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getIdFieldName',
                    'checkIdentifier',
                ]
            )
            ->getMock();
        $this->thisMock = $this->getMockBuilder('Magento\Cms\Model\Page')
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->getMockBuilder('Magento\Framework\Registry')
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this->getMockBuilder('Magento\Framework\Model\ResourceModel\AbstractResource')
                        ->disableOriginalConstructor()
                        ->setMethods(
                            [
                                '_construct',
                                'getConnection',
                            ]
                        )
                        ->getMockForAbstractClass(),
                    $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass(),
                ]
            )
            ->setMethods(
                [
                    '_construct',
                    '_getResource',
                    'load',
                ]
            )
            ->getMock();

        $this->thisMock->expects($this->any())
            ->method('_getResource')
            ->willReturn($this->resourcePageMock);
        $this->thisMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
    }

    /**
     * @covers \Magento\Cms\Model\Page::noRoutePage
     */
    public function testNoRoutePage()
    {
        $this->assertEquals($this->thisMock, $this->thisMock->noRoutePage());
    }

    /**
     * @covers \Magento\Cms\Model\Page::checkIdentifier
     */
    public function testCheckIdentifier()
    {
        $identifier = 1;
        $storeId = 2;
        $fetchOneResult = 'some result';

        $this->resourcePageMock->expects($this->atLeastOnce())
            ->method('checkIdentifier')
            ->with($identifier, $storeId)
            ->willReturn($fetchOneResult);

        $this->assertInternalType('string', $this->thisMock->checkIdentifier($identifier, $storeId));
    }
}
