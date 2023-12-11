<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cookie\Test\Unit\Model\Config\Backend;

use Magento\Cookie\Model\Config\Backend\Lifetime;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Session\Config\Validator\CookieLifetimeValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cookie\Model\Config\Backend\Lifetime
 */
class LifetimeTest extends TestCase
{
    /** @var MockObject|CookieLifetimeValidator */
    private $validatorMock;

    /** @var MockObject|ModuleResource */
    private $resourceMock;

    /** @var Lifetime */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockBuilder(CookieLifetimeValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(ModuleResource::class)
            ->disableOriginalConstructor('delete')
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Lifetime::class,
            [
                'configValidator' => $this->validatorMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     */
    public function testBeforeSaveException(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
    public function testBeforeSaveNoException(): void
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
    public function testBeforeEmptyString(): void
    {
        $validCookieLifetime = '';
        $this->validatorMock->expects($this->never())
            ->method('isValid');

        // Test
        $this->model->setValue($validCookieLifetime)->beforeSave();
    }
}
