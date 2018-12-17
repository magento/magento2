<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\System\Message\Media\Synchronization;

use Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Storage\Flag;
use PHPUnit\Framework\TestCase;

/**
 * Class ErrorTest
 *
 * @package Magento\AdminNotification\Test\Unit\Model\System\Message\Media\Synchronization
 */
class ErrorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $syncFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileStorage;

    /**
     * @var Error
     */
    protected $model;

    protected function setUp()
    {
        $this->syncFlagMock = $this->createPartialMock(
            Flag::class,
            ['setState', 'save', 'getFlagData']
        );

        $this->fileStorage = $this->createMock(Flag::class);
        $this->fileStorage->expects(static::any())->method('loadSelf')
            ->will(static::returnValue($this->syncFlagMock));

        $objectManagerHelper = new ObjectManager($this);
        $arguments = ['fileStorage' => $this->fileStorage];
        $this->model = $objectManagerHelper->getObject(
            Error::class,
            $arguments
        );
    }

    public function testGetText()
    {
        $messageText = 'We were unable to synchronize one or more media files.';

        static::assertContains($messageText, (string)$this->model->getText());
    }

    /**
     * @param bool $expectedFirstRun
     * @param array $data
     * @throws \Exception
     * @dataProvider isDisplayedDataProvider
     */
    public function testIsDisplayed($expectedFirstRun, $data)
    {
        $arguments = ['fileStorage' => $this->fileStorage];
        $objectManagerHelper = new ObjectManager($this);
        // create new instance to ensure that it hasn't been displayed yet (var $this->_isDisplayed is unset)
        /** @var Error $model */
        $model = $objectManagerHelper->getObject(
            Error::class,
            $arguments
        );

        $this->syncFlagMock->expects(static::any())->method('setState');
        $this->syncFlagMock->expects(static::any())->method('save');
        $this->syncFlagMock->expects(static::any())->method('getFlagData')->will(static::returnValue($data));
        //check first call
        static::assertEquals($expectedFirstRun, $model->isDisplayed());
        //check second call(another branch of if operator)
        static::assertEquals($expectedFirstRun, $model->isDisplayed());
    }

    /**
     * @return array
     */
    public function isDisplayedDataProvider()
    {
        return [
            [true, ['has_errors' => 1]],
            [true, ['has_errors' => true]],
            [false, []],
            [false, ['has_errors' => 0]]
        ];
    }

    public function testGetIdentity()
    {
        static::assertEquals('MEDIA_SYNCHRONIZATION_ERROR', $this->model->getIdentity());
    }

    public function testGetSeverity()
    {
        $severity = MessageInterface::SEVERITY_MAJOR;
        static::assertEquals($severity, $this->model->getSeverity());
    }
}
