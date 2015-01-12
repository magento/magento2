<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Shipment;

/**
 * Class CommentTest
 */
class CommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Comment
     */
    protected $commentResource;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentModelMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Comment\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->commentModelMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\Comment',
            [],
            [],
            '',
            false
        );
        $this->appResourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [],
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\Comment\Validator',
            [],
            [],
            '',
            false
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->adapterMock));
        $this->adapterMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->adapterMock->expects($this->any())
            ->method('insert');
        $this->adapterMock->expects($this->any())
            ->method('lastInsertId');
        $this->commentModelMock->expects($this->any())->method('hasDataChanges')->will($this->returnValue(true));
        $this->commentModelMock->expects($this->any())->method('isSaveAllowed')->will($this->returnValue(true));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->commentResource = $objectManager->getObject(
            'Magento\Sales\Model\Resource\Order\Shipment\Comment',
            [
                'resource' => $this->appResourceMock,
                'validator' => $this->validatorMock
            ]
        );
    }

    /**
     * Test _beforeSaveMethod via save()
     */
    public function testSave()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->commentModelMock))
            ->will($this->returnValue([]));
        $this->commentResource->save($this->commentModelMock);
        $this->assertTrue(true);
    }

    /**
     * Test _beforeSaveMethod via save() with failed validation
     *
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Cannot save comment:
     */
    public function testSaveValidationFailed()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->commentModelMock))
            ->will($this->returnValue(['warning message']));
        $this->commentResource->save($this->commentModelMock);
        $this->assertTrue(true);
    }
}
