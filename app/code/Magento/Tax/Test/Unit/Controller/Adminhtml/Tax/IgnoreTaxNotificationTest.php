<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Controller\Adminhtml\Tax;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Controller\Adminhtml\Tax\IgnoreTaxNotification;
use PHPUnit\Framework\TestCase;

class IgnoreTaxNotificationTest extends TestCase
{
    public function testExecute()
    {
        $objectManager = new ObjectManager($this);
        $cacheTypeList = $this->getMockBuilder(TypeList::class)
            ->disableOriginalConstructor()
            ->setMethods(['cleanType'])
            ->getMock();
        $cacheTypeList->expects($this->once())
            ->method('cleanType')
            ->with('config')
            ->willReturn(null);

        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();
        $request->expects($this->once())
            ->method('getParam')
            ->willReturn('tax');

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())
            ->method('setRefererUrl')
            ->willReturnSelf();

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveConfig'])
            ->getMock();
        $config->expects($this->once())
            ->method('saveConfig')
            ->with('tax/notification/ignore_tax', 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0)
            ->willReturn(null);

        $manager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create', 'configure'])
            ->getMockForAbstractClass();
        $manager->expects($this->any())
            ->method('get')
            ->willReturn($config);

        $notification = $objectManager->getObject(
            IgnoreTaxNotification::class,
            [
                'objectManager' => $manager,
                'cacheTypeList' => $cacheTypeList,
                'request' => $request,
                'resultFactory' => $resultFactory
            ]
        );

        // No exception thrown
        $this->assertSame($resultRedirect, $notification->execute());
    }
}
