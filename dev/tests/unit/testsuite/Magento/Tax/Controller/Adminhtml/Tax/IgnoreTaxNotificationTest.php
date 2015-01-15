<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Tax;

use Magento\TestFramework\Helper\ObjectManager;

class IgnoreTaxNotificationTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManager = new ObjectManager($this);
        $cacheTypeList = $this->getMockBuilder('\Magento\Framework\App\Cache\TypeList')
            ->disableOriginalConstructor()
            ->setMethods(['cleanType'])
            ->getMock();
        $cacheTypeList->expects($this->once())
            ->method('cleanType')
            ->with('block_html')
            ->will($this->returnValue(null));

        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();
        $request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue('tax'));

        $response = $this->getMockBuilder('\Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMock();

        $config = $this->getMockBuilder('\Magento\Core\Model\Resource\Config')
            ->disableOriginalConstructor()
            ->setMethods(['saveConfig'])
            ->getMock();
        $config->expects($this->once())
            ->method('saveConfig')
            ->with('tax/notification/ignore_tax', 1, \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, 0)
            ->will($this->returnValue(null));

        $manager = $this->getMockBuilder('\Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create', 'configure'])
            ->getMock();
        $manager->expects($this->any())
            ->method('get')
            ->will($this->returnValue($config));

        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Tax\IgnoreTaxNotification',
            [
                'objectManager' => $manager,
                'cacheTypeList' => $cacheTypeList,
                'request' => $request,
                'response' => $response
            ]
        );

        // No exception thrown
        $notification->execute();
    }
}
