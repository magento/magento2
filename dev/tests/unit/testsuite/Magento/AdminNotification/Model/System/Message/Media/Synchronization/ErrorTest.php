<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_syncFlagMock = $this->getMock('Magento\Core\Model\File\Storage\Flag', array(), array(), '', false);

        $this->_fileStorage = $this->getMock('Magento\Core\Model\File\Storage\Flag', array(), array(), '', false);
        $this->_fileStorage->expects($this->any())->method('loadSelf')->will($this->returnValue($this->_syncFlagMock));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array('fileStorage' => $this->_fileStorage);
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
        $arguments = array('fileStorage' => $this->_fileStorage);
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
        return array(
            array(true, array('has_errors' => 1)),
            array(true, array('has_errors' => true)),
            array(false, array()),
            array(false, array('has_errors' => 0))
        );
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
