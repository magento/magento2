<?php
/**
 * Unit test for Magento\Framework\ValidatorFactory
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RedirectFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->model = $objectManager->getObject('Magento\Framework\Controller\Result\RedirectFactory',
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $redirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($redirect);

        $resultRedirect = $this->model->create();
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Redirect', $resultRedirect);
        $this->assertSame($redirect, $resultRedirect);
    }
}
