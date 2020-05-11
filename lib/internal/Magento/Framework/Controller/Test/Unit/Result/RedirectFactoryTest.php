<?php declare(strict_types=1);
/**
 * Unit test for Magento\Framework\ValidatorFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\ValidatorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RedirectFactoryTest extends TestCase
{
    /** @var  ValidatorFactory */
    private $model;

    /** @var ObjectManagerInterface|MockObject */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = $objectManager->getObject(
            RedirectFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($redirect);

        $resultRedirect = $this->model->create();
        $this->assertInstanceOf(Redirect::class, $resultRedirect);
        $this->assertSame($redirect, $resultRedirect);
    }
}
