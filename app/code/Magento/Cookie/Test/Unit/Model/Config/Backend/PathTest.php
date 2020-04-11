<?php
/**
 * Unit test for Magento\Cookie\Model\Config\Backend\Path
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cookie\Test\Unit\Model\Config\Backend;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Module\ModuleResource;
use Magento\Cookie\Model\Config\Backend\Path;
use Magento\Framework\Session\Config\Validator\CookiePathValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PathTest extends TestCase
{
    /** @var MockObject|CookiePathValidator */
    private $validatorMock;

    /** @var MockObject|ModuleResource */
    private $resourceMock;

    /** @var Path */
    private $model;

    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockBuilder(
            CookiePathValidator::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(ModuleResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Path::class,
            [
                'configValidator' => $this->validatorMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     */
    public function testBeforeSaveException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid cookie path');
        $invalidCookiePath = 'invalid path';
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($invalidCookiePath)
            ->willReturn(false);

        // Must throw exception
        $this->model->setValue($invalidCookiePath)->beforeSave();
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * No assertions exist because the purpose of the test is to make sure that no
     * exception gets thrown
     */
    public function testBeforeSaveNoException()
    {
        $validCookiePath = 1;
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($validCookiePath)
            ->willReturn(true);
        $this->resourceMock->expects($this->any())->method('addCommitCallback')->willReturnSelf();

        // Must not throw exception
        $this->model->setValue($validCookiePath)->beforeSave();
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * Empty string should not be sent to validator
     */
    public function testBeforeSaveEmptyString()
    {
        $validCookiePath = '';
        $this->validatorMock->expects($this->never())
            ->method('isValid');

        $this->resourceMock->expects($this->any())->method('addCommitCallback')->willReturnSelf();

        // Must not throw exception
        $this->model->setValue($validCookiePath)->beforeSave();
    }
}
