<?php
/**
 * Unit test for Magento\Cookie\Model\Config\Backend\Lifetime
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cookie\Test\Unit\Model\Config\Backend;

use Magento\Framework\Session\Config\Validator\CookieLifetimeValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LifetimeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject | CookieLifetimeValidator */
    private $validatorMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Module\ModuleResource */
    private $resourceMock;

    /** @var \Magento\Cookie\Model\Config\Backend\Lifetime */
    private $model;

    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockBuilder(
            \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Module\ModuleResource::class)
            ->disableOriginalConstructor('delete')
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Cookie\Model\Config\Backend\Lifetime::class,
            [
                'configValidator' => $this->validatorMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     */
    public function testBeforeSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Invalid cookie lifetime: must be numeric');

        $invalidCookieLifetime = 'invalid lifetime';
        $messages = ['must be numeric'];
        $this->validatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($invalidCookieLifetime)
            ->willReturn(false);

        // Test
        $this->model->setValue($invalidCookieLifetime)->beforeSave();
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * No assertions exist because the purpose of the test is to make sure that no
     * exception gets thrown
     */
    public function testBeforeSaveNoException()
    {
        $validCookieLifetime = 1;
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($validCookieLifetime)
            ->willReturn(true);

        // Test
        $this->model->setValue($validCookieLifetime)->beforeSave();
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * No assertions exist because the purpose of the test is to make sure that no
     * exception gets thrown
     */
    public function testBeforeEmptyString()
    {
        $validCookieLifetime = '';
        $this->validatorMock->expects($this->never())
            ->method('isValid');

        // Test
        $this->model->setValue($validCookieLifetime)->beforeSave();
    }
}
