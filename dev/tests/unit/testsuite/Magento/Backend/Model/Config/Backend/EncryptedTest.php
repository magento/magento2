<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

        $eventDispatcherMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $contextMock = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $contextMock->expects(
            $this->any()
        )->method(
            'getEventDispatcher'
        )->will(
            $this->returnValue($eventDispatcherMock)
        );
        $this->_resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\AbstractResource',
            [
                '_construct',
                '_getReadAdapter',
                '_getWriteAdapter',
                'getIdFieldName',
                'beginTransaction',
                'save',
                'commit',
                'addCommitCallback'
            ],
            [],
            '',
            false
        );
        $this->_configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_helperMock = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->_encryptorMock = $this->getMock(
            'Magento\Framework\Encryption\EncryptorInterface',
            [],
            [],
            '',
            false
        );
        $this->_model = $helper->getObject(
            'Magento\Backend\Model\Config\Backend\Encrypted',
            [
                'config' => $this->_configMock,
                'context' => $contextMock,
                'resource' => $this->_resourceMock,
                'encryptor' => $this->_encryptorMock
            ]
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
     * @covers \Magento\Backend\Model\Config\Backend\Encrypted::beforeSave
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
        $this->_model->beforeSave();
        $this->assertEquals($this->_model->getValue(), 'encrypted');
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [['****', 'oldValue'], ['newValue', 'newValue']];
    }
}
