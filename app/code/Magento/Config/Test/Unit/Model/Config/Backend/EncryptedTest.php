<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EncryptedTest extends TestCase
{
    /** @var MockObject */
    protected $_encryptorMock;

    /** @var MockObject */
    protected $_configMock;

    /** @var MockObject */
    protected $_resourceMock;

    /** @var Encrypted */
    protected $_model;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $eventDispatcherMock = $this->createMock(Manager::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects(
            $this->any()
        )->method(
            'getEventDispatcher'
        )->willReturn(
            $eventDispatcherMock
        );
        $this->_resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['getIdFieldName', 'save'])
            ->onlyMethods(['getConnection', 'beginTransaction', 'commit', 'addCommitCallback'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_encryptorMock = $this->getMockForAbstractClass(EncryptorInterface::class);
        $this->_model = $helper->getObject(
            Encrypted::class,
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
        )->willReturn(
            $result
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
            ->willReturn('encrypted');

        $this->_model->setValue($value);
        $this->_model->setPath('some/path');
        $this->_model->beforeSave();

        $this->assertEquals($expectedValue, $this->_model->getValue());
    }

    /**
     * @return array
     */
    public static function beforeSaveDataProvider()
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
