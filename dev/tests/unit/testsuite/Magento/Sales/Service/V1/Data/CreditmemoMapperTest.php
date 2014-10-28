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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class CreditmemoMapperTest
 */
class CreditmemoMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Data\CreditmemoMapper
     */
    protected $creditmemoMapper;

    /**
     * @var \Magento\Sales\Service\V1\Data\CreditmemoBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoBuilderMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\CreditmemoItemMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoItemMapperMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoItemMock;

    /**
     * SetUp
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditmemoBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\CreditmemoBuilder',
            ['populateWithArray', 'setItems', 'create'],
            [],
            '',
            false
        );
        $this->creditmemoItemMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\CreditmemoItemMapper',
            ['extractDto'],
            [],
            '',
            false
        );
        $this->creditmemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            ['getAllItems', 'getData', '__wakeup'],
            [],
            '',
            false
        );
        $this->creditmemoItemMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo\Item',
            [],
            [],
            '',
            false
        );
        $this->creditmemoMapper = new \Magento\Sales\Service\V1\Data\CreditmemoMapper(
            $this->creditmemoBuilderMock,
            $this->creditmemoItemMapperMock
        );
    }

    /**
     * Run creditmemo mapper test
     *
     * @return void
     */
    public function testInvoke()
    {
        $this->creditmemoMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(['field-1' => 'value-1']));
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue([$this->creditmemoItemMock]));
        $this->creditmemoBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($this->equalTo(['field-1' => 'value-1']))
            ->will($this->returnSelf());
        $this->creditmemoItemMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->creditmemoItemMock))
            ->will($this->returnValue('item-1'));
        $this->creditmemoBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo(['item-1']))
            ->will($this->returnSelf());
        $this->creditmemoBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('data-object-with-creditmemo'));
        $this->assertEquals('data-object-with-creditmemo', $this->creditmemoMapper->extractDto($this->creditmemoMock));
    }
}
