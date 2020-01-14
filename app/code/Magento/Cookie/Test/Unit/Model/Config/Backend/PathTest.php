<?php
/**
 * Unit test for Magento\Cookie\Model\Config\Backend\Path
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cookie\Test\Unit\Model\Config\Backend;

use Magento\Framework\Session\Config\Validator\CookiePathValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PathTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | CookiePathValidator */
    private $validatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Module\ModuleResource */
    private $resourceMock;

    /** @var \Magento\Cookie\Model\Config\Backend\Path */
    private $model;

    protected function setUp()
    {
        $this->validatorMock = $this->getMockBuilder(
            \Magento\Framework\Session\Config\Validator\CookiePathValidator::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Module\ModuleResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Cookie\Model\Config\Backend\Path::class,
            [
                'configValidator' => $this->validatorMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid cookie path
     */
    public function testBeforeSaveException()
    {
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
