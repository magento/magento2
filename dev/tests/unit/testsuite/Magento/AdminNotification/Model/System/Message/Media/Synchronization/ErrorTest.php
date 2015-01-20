<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\System\Message\Media\Synchronization;

class ErrorTest extends \PHPUnit_Framework_TestCase
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
        $this->_syncFlagMock = $this->getMock('Magento\Core\Model\File\Storage\Flag', [], [], '', false);

        $this->_fileStorage = $this->getMock('Magento\Core\Model\File\Storage\Flag', [], [], '', false);
        $this->_fileStorage->expects($this->any())->method('loadSelf')->will($this->returnValue($this->_syncFlagMock));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = ['fileStorage' => $this->_fileStorage];
        $this->_model = $objectManagerHelper->getObject(
            'Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error',
            $arguments
        );
    }

    public function testGetText()
    {
        $messageText = 'One or more media files failed to be synchronized';

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
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        // create new instance to ensure that it hasn't been displayed yet (var $this->_isDisplayed is unset)
        /** @var $model \Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error */
        $model = $objectManagerHelper->getObject(
            'Magento\AdminNotification\Model\System\Message\Media\Synchronization\Error',
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
