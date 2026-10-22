<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EncryptedTest extends TestCase
{
    use MockCreationTrait;

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
        $this->_resourceMock = $this->createPartialMockWithReflection(
            AbstractResource::class,
            ['getIdFieldName', 'save', 'getConnection', 'beginTransaction', 'commit', 'addCommitCallback', '_construct']
        );
        $this->_configMock = $this->createMock(ScopeConfigInterface::class);
        $this->_encryptorMock = $this->createMock(EncryptorInterface::class);
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

    #[DataProvider('environmentPlaintextDataProvider')]
    public function testProcessEnvironmentValuePreservesPlaintext(string $value): void
    {
        $this->_encryptorMock->expects($this->never())->method('decrypt');

        $this->assertSame($value, $this->_model->processEnvironmentValue($value));
    }

    public static function environmentPlaintextDataProvider(): array
    {
        return [
            'plaintext' => ['plain secret'],
            'spaces' => [' spaced '],
            'empty' => [''],
            'zero' => ['0'],
            'placeholder' => ['{{secure_base_url}}'],
            'colon-separated plaintext' => ['abc:def:ghi'],
            'invalid base64' => ['0:3:not base64!'],
            'trailing newline' => ["0:3:QUJD\n"],
            'one-part legacy format' => ['QUJD'],
            'two-part legacy format' => ['0:QUJD'],
        ];
    }

    public function testProcessEnvironmentValueDecryptsCurrentFormat(): void
    {
        $value = '0:3:QUJD';
        $this->_encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($value)
            ->willReturn('decrypted secret');

        $this->assertSame('decrypted secret', $this->_model->processEnvironmentValue($value));
    }

    #[DataProvider('emptyDecryptionResultDataProvider')]
    public function testProcessEnvironmentValuePreservesValueOnEmptyDecryption(?string $result): void
    {
        $value = '12:34:abcd';
        $this->_encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($value)
            ->willReturn($result);

        $this->assertSame($value, $this->_model->processEnvironmentValue($value));
    }

    public static function emptyDecryptionResultDataProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
        ];
    }

    public function testProcessEnvironmentValuePreservesValueOnDecryptionException(): void
    {
        $value = '0:99:AAAA';
        $this->_encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($value)
            ->willThrowException(new \Exception('Unsupported cipher version'));

        $this->assertSame($value, $this->_model->processEnvironmentValue($value));
    }

    public function testProcessEnvironmentValuePreservesNull(): void
    {
        $this->_encryptorMock->expects($this->never())->method('decrypt');

        $this->assertNull($this->_model->processEnvironmentValue(null));
    }

    /**
     * @covers \Magento\Config\Model\Config\Backend\Encrypted::beforeSave
     *
     * @param string $value
     * @param string $expectedValue
     * @param int $encryptMethodCall
     */
    #[DataProvider('beforeSaveDataProvider')]
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
