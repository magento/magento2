<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Controller\Index;

use Magento\TestFramework\Helper\ObjectManager;

class NotFoundTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Response\Http
         */
        $responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock->expects($this->once())->method('setHeader')->with('HTTP/1.1', '404 Not Found');
        $responseMock->expects($this->once())->method('setHttpResponseCode')->with(404);
        $responseMock->expects($this->once())->method('setBody')->with('Requested resource not found');

        $objectManager = new ObjectManager($this);

        /**
         * @var \Magento\Core\Controller\Index
         */
        $controller = $objectManager->getObject(
            'Magento\Core\Controller\Index\NotFound',
            ['response' => $responseMock]
        );

        // Make the call to test
        $controller->execute();
    }
}
