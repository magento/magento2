<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Cryptographer;
use Magento\Analytics\Model\EncodedContext;
use Magento\Analytics\Model\EncodedContextFactory;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CryptographerTest extends TestCase
{
    /**
     * @var AnalyticsToken|MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var EncodedContextFactory|MockObject
     */
    private $encodedContextFactoryMock;

    /**
     * @var EncodedContext|MockObject
     */
    private $encodedContextMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Cryptographer
     */
    private $cryptographer;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $initializationVectors;
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $cipherMethod = 'AES-256-CBC';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->analyticsTokenMock = $this->createMock(AnalyticsToken::class);

        $this->encodedContextFactoryMock = $this->getMockBuilder(EncodedContextFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->encodedContextMock = $this->createMock(EncodedContext::class);

        $this->key = '';
        $this->source = '';
        $this->initializationVectors = [];

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->cryptographer = $this->objectManagerHelper->getObject(
            Cryptographer::class,
            [
                'analyticsToken' => $this->analyticsTokenMock,
                'encodedContextFactory' => $this->encodedContextFactoryMock,
                'cipherMethod' => $this->cipherMethod,
            ]
        );
    }

    /**
     * @return void
     */
    public function testEncode()
    {
        $token = 'some-token-value';
        $this->source = 'Some text';
        $this->key = hash('sha256', $token);

        $checkEncodedContext = function ($parameters) {
            $emptyRequiredParameters =
                array_diff(['content', 'initializationVector'], array_keys(array_filter($parameters)));
            if ($emptyRequiredParameters) {
                return false;
            }

            $encryptedData = openssl_encrypt(
                $this->source,
                $this->cipherMethod,
                $this->key,
                OPENSSL_RAW_DATA,
                $parameters['initializationVector']
            );

            return ($encryptedData === $parameters['content']);
        };

        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('getToken')
            ->with()
            ->willReturn($token);

        $this->encodedContextFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback($checkEncodedContext))
            ->willReturn($this->encodedContextMock);

        $this->assertSame($this->encodedContextMock, $this->cryptographer->encode($this->source));
    }

    /**
     * @return void
     */
    public function testEncodeUniqueInitializationVector()
    {
        $this->source = 'Some text';
        $token = 'some-token-value';

        $registerInitializationVector = function ($parameters) {
            if (empty($parameters['initializationVector'])) {
                return false;
            }

            $this->initializationVectors[] = $parameters['initializationVector'];

            return true;
        };

        $this->analyticsTokenMock
            ->expects($this->exactly(2))
            ->method('getToken')
            ->with()
            ->willReturn($token);

        $this->encodedContextFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->with($this->callback($registerInitializationVector))
            ->willReturn($this->encodedContextMock);

        $this->assertSame($this->encodedContextMock, $this->cryptographer->encode($this->source));
        $this->assertSame($this->encodedContextMock, $this->cryptographer->encode($this->source));
        $this->assertCount(2, array_unique($this->initializationVectors));
    }

    /**
     * Streaming encryption must yield cipher byte-for-byte identical to a single-pass openssl_encrypt,
     * so the encrypted archive stays decryptable with the returned initialization vector.
     *
     * @param int $sourceLength
     * @return void
     */
    #[DataProvider('encodeToFileDataProvider')]
    public function testEncodeToFileMatchesSinglePass($sourceLength)
    {
        $token = 'some-token-value';
        $key = hash('sha256', $token);
        $source = $sourceLength > 0 ? random_bytes($sourceLength) : '';

        $this->analyticsTokenMock
            ->method('getToken')
            ->willReturn($token);

        $capturedVector = null;
        $this->encodedContextFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($parameters) use (&$capturedVector) {
                $capturedVector = $parameters['initializationVector'];
                return $this->encodedContextMock;
            });

        // Emit the source in small pieces to exercise the block-alignment carry across chunks.
        $offset = 0;
        $sourceMock = $this->createMock(FileReadInterface::class);
        $sourceMock->method('read')->willReturnCallback(
            function ($length) use (&$offset, $source) {
                if ($offset >= strlen($source)) {
                    return '';
                }
                $chunk = substr($source, $offset, min((int)$length, 100));
                $offset += strlen($chunk);
                return $chunk;
            }
        );

        $written = '';
        $destinationMock = $this->createMock(FileWriteInterface::class);
        $destinationMock->method('write')->willReturnCallback(
            function ($data) use (&$written) {
                $written .= $data;
                return strlen($data);
            }
        );

        $result = $this->cryptographer->encodeToFile($sourceMock, $destinationMock);

        $this->assertSame($this->encodedContextMock, $result);
        $expected = openssl_encrypt($source, $this->cipherMethod, $key, OPENSSL_RAW_DATA, $capturedVector);
        $this->assertSame($expected, $written);
    }

    /**
     * @return array
     */
    public static function encodeToFileDataProvider()
    {
        return [
            'Shorter than one block' => [9],
            'Exactly one block' => [16],
            'One block plus a byte' => [17],
            'Two blocks' => [32],
            'Spanning multiple read chunks' => [5003],
        ];
    }

    /**
     * An empty source must be rejected, mirroring encode()'s empty-input guard.
     *
     * @return void
     */
    public function testEncodeToFileThrowsOnEmptySource()
    {
        $this->analyticsTokenMock
            ->method('getToken')
            ->willReturn('some-token-value');

        $sourceMock = $this->createMock(FileReadInterface::class);
        $sourceMock->method('read')->willReturn('');

        $destinationMock = $this->createMock(FileWriteInterface::class);
        $destinationMock->expects($this->never())->method('write');

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->cryptographer->encodeToFile($sourceMock, $destinationMock);
    }

    #[DataProvider('encodeNotValidSourceDataProvider')]
    public function testEncodeNotValidSource($source)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->cryptographer->encode($source);
    }

    /**
     * @return array
     */
    public static function encodeNotValidSourceDataProvider()
    {
        return [
            'Array' => [[]],
            'Empty string' => [''],
        ];
    }

    public function testEncodeNotValidCipherMethod()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $source = 'Some string';
        $cryptographer = $this->objectManagerHelper->getObject(
            Cryptographer::class,
            [
                'cipherMethod' => 'Wrong-method',
            ]
        );

        $cryptographer->encode($source);
    }

    public function testEncodeTokenNotValid()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $source = 'Some string';

        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('getToken')
            ->with()
            ->willReturn(null);

        $this->cryptographer->encode($source);
    }
}
