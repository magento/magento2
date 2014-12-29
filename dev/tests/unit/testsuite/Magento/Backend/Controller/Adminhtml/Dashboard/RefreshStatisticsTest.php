<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics
 */
class RefreshStatisticsTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $path = '*/*';

        $reportTypes = [
            'sales' => 'Magento\Sales\Model\Resource\Report\Order'
        ];

        $request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $response->expects($this->once())->method('setRedirect')->with($path);

        $messageManager = $this->getMock('\Magento\Framework\Message\Manager', [], [], '', false);
        $messageManager->expects($this->once())->method('addSuccess')->with(__('We updated lifetime statistic.'));

        $adminSession = $this->getMock('Magento\Backend\Model\Auth\Session', ['setIsUrlNotice'], [], '', false);
        $adminSession->expects($this->atLeastOnce())->method('setIsUrlNotice')->with(true);

        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', ['get'], [], '', false);
        $actionFlag->expects($this->atLeastOnce())
            ->method('get')
            ->with('', 'check_url_settings')
            ->will($this->returnValue(true));

        $date = $this->getMock('Magento\Framework\Stdlib\DateTime\Filter\Date', [], [], '', false);

        $order = $this->getMock('Magento\Sales\Model\Resource\Report\Order', [], [], '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->any())->method('create')->with('Magento\Sales\Model\Resource\Report\Order')
            ->will($this->returnValue($order));
        $objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Backend\Model\Auth\Session')
            ->will($this->returnValue($adminSession));

        $helper = $this->getMock('\Magento\Backend\Helper\Data', ['getUrl'], [], '', false);
        $helper->expects($this->atLeastOnce())->method('getUrl')->with($path)->will($this->returnValue($path));

        $context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($response));
        $context->expects($this->once())->method('getMessageManager')->will($this->returnValue($messageManager));
        $context->expects($this->once())->method('getActionFlag')->will($this->returnValue($actionFlag));
        $context->expects($this->any())->method('getObjectManager')->will($this->returnValue($objectManager));
        $context->expects($this->atLeastOnce())->method('getHelper')->will($this->returnValue($helper));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics $refreshStatisticsController */
        $refreshStatisticsController = $objectManagerHelper->getObject(
            'Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics',
            [
                'context' => $context,
                'dateFilter' => $date,
                'reportTypes' => $reportTypes
            ]
        );

        $refreshStatisticsController->execute();
    }
}
