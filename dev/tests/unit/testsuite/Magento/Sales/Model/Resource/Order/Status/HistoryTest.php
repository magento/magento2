<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Order\Status;

/**
 * Class HistoryTest
 * @package Magento\Sales\Model\Resource\Order\Status
 */
class HistoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\History
     */
    protected $historyResource;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;


    public function setUp()
    {
        $this->appResourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['describeTable', 'insert', 'lastInsertId'],
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History\Validator',
            [],
            [],
            '',
            false
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->adapterMock));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->adapterMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->adapterMock->expects($this->any())
            ->method('insert');
        $this->adapterMock->expects($this->any())
            ->method('lastInsertId');
        $this->historyResource = $objectManager->getObject(
            'Magento\Sales\Model\Resource\Order\Status\History',
            [
                'resource' => $this->appResourceMock,
                'validator' => $this->validatorMock
            ]
        );

    }

    /**
     * test _beforeSaveMethod via save()
     */
    public function testSave()
    {
        $historyMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History',
            [],
            [],
            '',
            false
        );
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->will($this->returnValue([]));
        $this->historyResource->save($historyMock);
    }
    
    /**
     * test _beforeSaveMethod via save()
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Cannot save comment:
     */
    public function testValidate()
    {
        $historyMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History',
            [],
            [],
            '',
            false
        );
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->will($this->returnValue(['Some warnings']));
        $this->assertEquals($this->historyResource, $this->historyResource->save($historyMock));
    }
}
