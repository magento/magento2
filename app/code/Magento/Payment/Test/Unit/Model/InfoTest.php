<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Info;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Substitution;
use Magento\Payment\Model\MethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends TestCase
{
    /**
     * @var InfoInterface
     */
    protected $info;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var Data|MockObject
     */
    protected $paymentHelperMock;

    /**
     * @var EncryptorInterface|MockObject
     */
    protected $encryptorInterfaceMock;

    /**
     * @var Data|MockObject
     */
    protected $methodInstanceMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->paymentHelperMock = $this->createPartialMock(Data::class, ['getMethodInstance']);
        $this->encryptorInterfaceMock = $this->getMockForAbstractClass(EncryptorInterface::class);
        $this->methodInstanceMock = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->info = $this->objectManagerHelper->getObject(
            Info::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'paymentData' => $this->paymentHelperMock,
                'encryptor' => $this->encryptorInterfaceMock
            ]
        );
    }

    /**
     * @dataProvider ccKeysDataProvider
     * @param string $keyCc
     * @param string $keyCcEnc
     *
     * @return void
     */
    public function testGetDataCcNumber(string $keyCc, string $keyCcEnc): void
    {
        // no data was set
        $this->assertNull($this->info->getData($keyCc));

        // we set encrypted data
        $this->info->setData($keyCcEnc, $keyCcEnc);
        $this->encryptorInterfaceMock->expects($this->once())->method('decrypt')->with($keyCcEnc)->willReturn(
            $keyCc
        );
        $this->assertEquals($keyCc, $this->info->getData($keyCc));
    }

    /**
     * Returns array of Cc keys which needs prepare logic
     *
     * @return array
     */
    public static function ccKeysDataProvider(): array
    {
        return [
            ['cc_number', 'cc_number_enc'],
            ['cc_cid', 'cc_cid_enc']
        ];
    }

    /**
     * @return void
     */
    public function testGetMethodInstanceWithRealMethod(): void
    {
        $method = 'real_method';
        $this->info->setData('method', $method);

        $this->methodInstanceMock->expects($this->once())
            ->method('setInfoInstance')
            ->with($this->info);

        $this->paymentHelperMock->expects($this->once())
            ->method('getMethodInstance')
            ->with($method)
            ->willReturn($this->methodInstanceMock);

        $this->info->getMethodInstance();
    }

    /**
     * @return void
     */
    public function testGetMethodInstanceWithUnrealMethod(): void
    {
        $method = 'unreal_method';
        $this->info->setData('method', $method);

        $this->methodInstanceMock->expects($this->once())
            ->method('setInfoInstance')
            ->with($this->info);

        $this->paymentHelperMock
            ->method('getMethodInstance')
            ->willReturnCallback(function ($arg) use ($method) {
                if ($arg == $method) {
                    return $this->methodInstanceMock;
                } elseif ($arg == Substitution::CODE) {
                    return $this->methodInstanceMock;
                }
            });

        $this->info->getMethodInstance();
    }

    /**
     * @return void
     */
    public function testGetMethodInstanceWithNoMethod(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The payment method you requested is not available.');
        $this->info->setData('method', false);
        $this->info->getMethodInstance();
    }

    /**
     * @return void
     */
    public function testGetMethodInstanceRequestedMethod(): void
    {
        $code = 'real_method';
        $this->info->setData('method', $code);

        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($code)->willReturn(
            $this->methodInstanceMock
        );

        $this->methodInstanceMock->expects($this->once())->method('setInfoInstance')->with($this->info);
        $this->assertSame($this->methodInstanceMock, $this->info->getMethodInstance());

        // as the method is already stored at Info, check that it's not initialized again
        $this->assertSame($this->methodInstanceMock, $this->info->getMethodInstance());
    }

    /**
     * @return void
     */
    public function testEncrypt(): void
    {
        $data = 'data';
        $encryptedData = 'd1a2t3a4';

        $this->encryptorInterfaceMock->expects($this->once())->method('encrypt')->with($data)->willReturn(
            $encryptedData
        );
        $this->assertEquals($encryptedData, $this->info->encrypt($data));
    }

    /**
     * @return void
     */
    public function testDecrypt(): void
    {
        $data = 'data';
        $encryptedData = 'd1a2t3a4';

        $this->encryptorInterfaceMock->expects($this->once())->method('decrypt')->with($encryptedData)->willReturn(
            $data
        );
        $this->assertEquals($data, $this->info->decrypt($encryptedData));
    }

    /**
     * @return void
     */
    public function testSetAdditionalInformationException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->info->setAdditionalInformation('object', new \StdClass());
    }

    /**
     * @dataProvider additionalInformationDataProvider
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function testSetAdditionalInformationMultipleTypes($key, $value = null): void
    {
        $this->info->setAdditionalInformation($key, $value);
        $this->assertEquals($value ? [$key => $value] : $key, $this->info->getAdditionalInformation());
    }

    /**
     * Prepared data for testSetAdditionalInformationMultipleTypes
     *
     * @return array
     */
    public static function additionalInformationDataProvider(): array
    {
        return [
            [['key1' => 'data1', 'key2' => 'data2'], null],
            ['key', 'data']
        ];
    }

    /**
     * @return void
     */
    public function testGetAdditionalInformationByKey(): void
    {
        $key = 'key';
        $value = 'value';
        $this->info->setAdditionalInformation($key, $value);
        $this->assertEquals($value, $this->info->getAdditionalInformation($key));
    }

    /**
     * @return void
     */
    public function testUnsAdditionalInformation(): void
    {
        // set array to additional
        $data = ['key1' => 'data1', 'key2' => 'data2'];
        $this->info->setAdditionalInformation($data);

        // unset by key
        $this->assertEquals(
            ['key2' => 'data2'],
            $this->info->unsAdditionalInformation('key1')->getAdditionalInformation()
        );

        // unset all
        $this->assertEmpty($this->info->unsAdditionalInformation()->getAdditionalInformation());
    }

    /**
     * @return void
     */
    public function testHasAdditionalInformation(): void
    {
        $this->assertFalse($this->info->hasAdditionalInformation());

        $data = ['key1' => 'data1', 'key2' => 'data2'];
        $this->info->setAdditionalInformation($data);
        $this->assertFalse($this->info->hasAdditionalInformation('key3'));

        $this->assertTrue($this->info->hasAdditionalInformation('key2'));
        $this->assertTrue($this->info->hasAdditionalInformation());
    }

    /**
     * @return void
     */
    public function testInitAdditionalInformationWithUnserialize(): void
    {
        $data = ['key1' => 'data1', 'key2' => 'data2'];
        $this->info->setData('additional_information', $data);

        $this->assertEquals($data, $this->info->getAdditionalInformation());
    }
}
