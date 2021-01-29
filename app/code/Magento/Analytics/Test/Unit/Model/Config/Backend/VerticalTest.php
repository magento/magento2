<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Config\Backend;

/**
 * A unit test for testing of the backend model for verticals configuration.
 */
class VerticalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Analytics\Model\Config\Backend\Vertical
     */
    private $subject;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\Model\Config\Backend\Vertical::class
        );
    }

    /**
     * @return void
     */
    public function testBeforeSaveSuccess()
    {
        $this->subject->setValue('Apps and Games');

        $this->assertInstanceOf(
            \Magento\Analytics\Model\Config\Backend\Vertical::class,
            $this->subject->beforeSave()
        );
    }

    /**
     * @return void
     */
    public function testBeforeSaveFailedWithLocalizedException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->subject->setValue('');

        $this->subject->beforeSave();
    }
}
