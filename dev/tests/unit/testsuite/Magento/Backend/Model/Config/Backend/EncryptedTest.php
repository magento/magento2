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
namespace Magento\Backend\Model\Config\Backend;

class EncryptedTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_encryptorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceMock;

    /** @var \Magento\Backend\Model\Config\Backend\Encrypted */
    protected $_model;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $eventDispatcherMock = $this->getMock('Magento\Framework\Event\Manager', array(), array(), '', false);
        $contextMock = $this->getMock('Magento\Framework\Model\Context', array(), array(), '', false);
        $contextMock->expects(
            $this->any()
        )->method(
            'getEventDispatcher'
        )->will(
            $this->returnValue($eventDispatcherMock)
        );
        $this->_resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\AbstractResource',
            array(
                '_construct',
                '_getReadAdapter',
                '_getWriteAdapter',
                'getIdFieldName',
                'beginTransaction',
                'save',
                'commit',
                'addCommitCallback'
            ),
            array(),
            '',
            false
        );
        $this->_configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_helperMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_encryptorMock = $this->getMock(
            'Magento\Framework\Encryption\EncryptorInterface',
            array(),
            array(),
            '',
            false
        );
        $this->_model = $helper->getObject(
            'Magento\Backend\Model\Config\Backend\Encrypted',
            array(
                'config' => $this->_configMock,
                'context' => $contextMock,
                'resource' => $this->_resourceMock,
                'encryptor' => $this->_encryptorMock
            )
        );
    }

    public function testProcessValue()
    {
        $value = 'someValue';
        $result = 'some value from parent class';
        $this->_encryptorMock->expects(
            $this->once()
        )->method(
            'decrypt'
        )->with(
            $value
        )->will(
            $this->returnValue($result)
        );
        $this->assertEquals($result, $this->_model->processValue($value));
    }

    /**
     * @covers \Magento\Backend\Model\Config\Backend\Encrypted::_beforeSave
     * @dataProvider beforeSaveDataProvider
     *
     * @param $value
     * @param $valueToSave
     */
    public function testBeforeSave($value, $valueToSave)
    {
        $this->_resourceMock->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());
        $this->_resourceMock->expects($this->any())->method('commit')->will($this->returnSelf());

        $this->_configMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            'some/path'
        )->will(
            $this->returnValue('oldValue')
        );
        $this->_encryptorMock->expects(
            $this->once()
        )->method(
            'encrypt'
        )->with(
            $valueToSave
        )->will(
            $this->returnValue('encrypted')
        );

        $this->_model->setValue($value);
        $this->_model->setPath('some/path');
        $this->_model->save();
        $this->assertEquals($this->_model->getValue(), 'encrypted');
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return array(array('****', 'oldValue'), array('newValue', 'newValue'));
    }
}
