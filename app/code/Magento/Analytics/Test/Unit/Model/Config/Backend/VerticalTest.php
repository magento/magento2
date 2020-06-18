<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\Vertical;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the backend model for verticals configuration.
 */
class VerticalTest extends TestCase
{
    /**
     * @var Vertical
     */
    private $subject;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper =
            new ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            Vertical::class
        );
    }

    /**
     * @return void
     */
    public function testBeforeSaveSuccess()
    {
        $this->subject->setValue('Apps and Games');

        $this->assertInstanceOf(
            Vertical::class,
            $this->subject->beforeSave()
        );
    }

    /**
     * @return void
     */
    public function testBeforeSaveFailedWithLocalizedException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->subject->setValue('');

        $this->subject->beforeSave();
    }
}
