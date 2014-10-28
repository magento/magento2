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

namespace Magento\Sales\Model\Grid\Child;

class CollectionUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Grid\Child\CollectionUpdater
     */
    protected $collectionUpdater;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;


    protected function setUp()
    {
        $this->registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);

        $this->collectionUpdater = new \Magento\Sales\Model\Grid\Child\CollectionUpdater(
            $this->registryMock
        );
    }

    public function testUpdateIfOrderExists()
    {
        $collectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Transaction\Collection', array(), array(), '', false
        );
        $transactionMock = $this->getMock('Magento\Sales\Model\Order\Payment\Transaction', array(), array(), '', false);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('current_transaction')
            ->will($this->returnValue($transactionMock));
        $transactionMock->expects($this->once())->method('getId')->will($this->returnValue('transactionId'));
        $collectionMock->expects($this->once())->method('addParentIdFilter')->will($this->returnSelf());
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }

}
