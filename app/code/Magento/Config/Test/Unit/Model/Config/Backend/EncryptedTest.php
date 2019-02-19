<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Backend;

class EncryptedTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_encryptorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceMock;

    /** @var \Magento\Config\Model\Config\Backend\Encrypted */
    protected $_model;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $eventDispatcherMock = $this->createMock(\Magento\Framework\Event\Manager::class);
        $contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $contextMock->expects(
            $this->any()
        )->method(
            'getEventDispatcher'
        )->will(
            $this->returnValue($eventDispatcherMock)
        );
        $this->_resourceMock = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\AbstractResource::class,
            [
                '_construct',
                'getConnection',
                'getIdFieldName',
                'beginTransaction',
                'save',
                'commit',
                'addCommitCallback',
            ]
        );
        $this->_configMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_encryptorMock = $this->createMock(\Magento\Framework\Encryption\EncryptorInterface::class);
        $this->_model = $helper->getObject(
            \Magento\Config\Model\Config\Backend\Encrypted::class,
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
     * @covers \Magento\Config\Model\Config\Backend\Encrypted::beforeSave
     * @dataProvider beforeSaveDataProvider
     *
     * @param string $value
     * @param string $expectedValue
     * @param int $encryptMethodCall
     */
    public function testBeforeSave($value, $expectedValue, $encryptMethodCall)
    {
        $this->_encryptorMock->expects($this->exactly($encryptMethodCall))
            ->method('encrypt')
            ->with($value)
            ->will($this->returnValue('encrypted'));

        $this->_model->setValue($value);
        $this->_model->setPath('some/path');
        $this->_model->beforeSave();

        $this->assertEquals($expectedValue, $this->_model->getValue());
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [['someValue', 'encrypted', 1], ['****', '****', 0]];
    }

    /**
     * @covers \Magento\Config\Model\Config\Backend\Encrypted::beforeSave
     */
    public function testAllowEmptySave()
    {
        $this->_model->setValue('');
        $this->_model->setPath('some/path');
        $this->_model->beforeSave();
        $this->assertTrue($this->_model->isSaveAllowed());
    }
}
