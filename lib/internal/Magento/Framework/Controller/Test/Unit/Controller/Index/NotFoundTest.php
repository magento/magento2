<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Controller\Test\Unit\Controller\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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

        $responseMock->expects($this->once())->method('setStatusHeader')->with(404, '1.1', 'Not Found');
        $responseMock->expects($this->once())->method('setBody')->with('Requested resource not found');

        $objectManager = new ObjectManager($this);

        /**
         * @var \Magento\Framework\Controller\Index\NotFound
         */
        $controller = $objectManager->getObject(
            'Magento\Framework\Controller\Index\NotFound',
            ['response' => $responseMock]
        );

        // Make the call to test
        $controller->execute();
    }
}
