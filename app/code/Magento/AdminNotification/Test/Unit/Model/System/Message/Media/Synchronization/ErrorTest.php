<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Unit\Model\System\Message\Media\Synchronization;

class ErrorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_syncFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileStorage;

    /**
     * @var \Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error
     */
    protected $_model;

    protected function setUp()
    {
        $this->_syncFlagMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\Flag::class,
            ['setState', 'save', 'getFlagData']
        );

        $this->_fileStorage = $this->createMock(\Magento\MediaStorage\Model\File\Storage\Flag::class);
        $this->_fileStorage->expects($this->any())->method('loadSelf')->will($this->returnValue($this->_syncFlagMock));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = ['fileStorage' => $this->_fileStorage];
        $this->_model = $objectManagerHelper->getObject(
            \Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error::class,
            $arguments
        );
    }

    public function testGetText()
    {
        $messageText = 'We were unable to synchronize one or more media files.';

        $this->assertContains($messageText, (string)$this->_model->getText());
    }

    /**
     * @param bool $expectedFirstRun
     * @param array $data
     * @dataProvider isDisplayedDataProvider
     */
    public function testIsDisplayed($expectedFirstRun, $data)
    {
        $arguments = ['fileStorage' => $this->_fileStorage];
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        // create new instance to ensure that it hasn't been displayed yet (var $this->_isDisplayed is unset)
        /** @var $model \Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error */
        $model = $objectManagerHelper->getObject(
            \Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error::class,
            $arguments
        );

        $this->_syncFlagMock->expects($this->any())->method('setState');
        $this->_syncFlagMock->expects($this->any())->method('save');
        $this->_syncFlagMock->expects($this->any())->method('getFlagData')->will($this->returnValue($data));
        //check first call
        $this->assertEquals($expectedFirstRun, $model->isDisplayed());
        //check second call(another branch of if operator)
        $this->assertEquals($expectedFirstRun, $model->isDisplayed());
    }

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
        $this->assertEquals('MEDIA_SYNCHRONIZATION_ERROR', $this->_model->getIdentity());
    }

    public function testGetSeverity()
    {
        $severity = \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;
        $this->assertEquals($severity, $this->_model->getSeverity());
    }
}
