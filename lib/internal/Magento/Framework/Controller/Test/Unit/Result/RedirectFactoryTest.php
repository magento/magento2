<?php
/**
 * Unit test for Magento\Framework\ValidatorFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RedirectFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = $objectManager->getObject(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($redirect);

        $resultRedirect = $this->model->create();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $resultRedirect);
        $this->assertSame($redirect, $resultRedirect);
    }
}
