<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\System\Message\Media\Synchronization;

use Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Storage\Flag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_syncFlagMock;

    /**
     * @var MockObject
     */
    protected $_fileStorage;

    /**
     * @var Error
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_syncFlagMock = $this->createPartialMock(
            Flag::class,
            ['save', 'getFlagData']
        );

        $this->_fileStorage = $this->createMock(Flag::class);
        $this->_fileStorage->method('loadSelf')->willReturn($this->_syncFlagMock);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = ['fileStorage' => $this->_fileStorage];
        $this->_model = $objectManagerHelper->getObject(
            Error::class,
            $arguments
        );
    }

    public function testGetText()
    {
        $messageText = 'We were unable to synchronize one or more media files.';
        $this->assertStringContainsString($messageText, (string)$this->_model->getText());
    }

    /**
     * @param bool $expectedFirstRun
     * @param array $data
     * @dataProvider isDisplayedDataProvider
     */
    public function testIsDisplayed($expectedFirstRun, $data)
    {
        $arguments = ['fileStorage' => $this->_fileStorage];
        $objectManagerHelper = new ObjectManager($this);
        // create new instance to ensure that it hasn't been displayed yet (var $this->_isDisplayed is unset)
        /** @var Error $model */
        $model = $objectManagerHelper->getObject(
            Error::class,
            $arguments
        );

        $this->_syncFlagMock->method('save');
        $this->_syncFlagMock->method('getFlagData')->willReturn($data);
        //check first call
        $this->assertEquals($expectedFirstRun, $model->isDisplayed());
        //check second call(another branch of if operator)
        $this->assertEquals($expectedFirstRun, $model->isDisplayed());
    }

    /**
     * @return array
     */
    public static function isDisplayedDataProvider()
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
        $this->assertEquals('MEDIA_SYNCHRONIZATION_ERROR', $this->_model->getIdentity());
    }

    public function testGetSeverity()
    {
        $severity = MessageInterface::SEVERITY_MAJOR;
        $this->assertEquals($severity, $this->_model->getSeverity());
    }
}
