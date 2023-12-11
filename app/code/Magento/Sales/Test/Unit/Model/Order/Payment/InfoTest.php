<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Substitution;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\Info;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\LocalizedException;

/**
 * Test for \Magento\Sales\Model\Order\Payment\Info.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends TestCase
{
    /**
     * @var Info
     */
    private $info;

    /**
     * @var Data|MockObject
     */
    private $paymentHelperMock;

    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptorInterfaceMock;

    /**
     * @var Data|MockObject
     */
    private $methodInstanceMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $registryMock = $this->createMock(Registry::class);
        $this->paymentHelperMock = $this->createPartialMock(Data::class, ['getMethodInstance']);
        $this->encryptorInterfaceMock = $this->getMockForAbstractClass(EncryptorInterface::class);
        $this->methodInstanceMock = $this->getMockForAbstractClass(MethodInterface::class);
        $this->orderMock = $this->createMock(OrderInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->info = $objectManagerHelper->getObject(
            Info::class,
            [
                'context' => $contextMock,
                'registry' => $registryMock,
                'paymentData' => $this->paymentHelperMock,
                'encryptor' => $this->encryptorInterfaceMock
            ]
        );
        $this->info->setData('order', $this->orderMock);
    }

    /**
     * Get data cc number
     *
     * @param string $keyCc
     * @param string $keyCcEnc
     *
     * @return void
     * @dataProvider ccKeysDataProvider
     */
    public function testGetDataCcNumber(string $keyCc, string $keyCcEnc): void
    {
        // no data was set
        $this->assertNull($this->info->getData($keyCc));

        // we set encrypted data
        $this->info->setData($keyCcEnc, $keyCcEnc);
        $this->encryptorInterfaceMock->expects($this->once())
            ->method('decrypt')
            ->with($keyCcEnc)
            ->willReturn($keyCc);

        $this->assertEquals($keyCc, $this->info->getData($keyCc));
    }

    /**
     * Returns array of Cc keys which needs prepare logic
     *
     * @return array
     */
    public function ccKeysDataProvider(): array
    {
        return [
            ['cc_number', 'cc_number_enc'],
            ['cc_cid', 'cc_cid_enc']
        ];
    }

    /**
     * Get method instance with real method
     *
     * @return void
     */
    public function testGetMethodInstanceWithRealMethod(): void
    {
        $storeId = 2;
        $method = 'real_method';
        $this->info->setData('method', $method);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->methodInstanceMock->expects($this->once())
            ->method('setInfoInstance')
            ->with($this->info);
        $this->methodInstanceMock->expects($this->once())
            ->method('setStore')
            ->with($storeId);

        $this->paymentHelperMock->expects($this->once())
            ->method('getMethodInstance')
            ->with($method)
            ->willReturn($this->methodInstanceMock);

        $this->info->getMethodInstance();
    }

    /**
     * Get method instance with unreal method
     *
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
            ->withConsecutive([$method], [Substitution::CODE])
            ->willReturn($this->methodInstanceMock);

        $this->info->getMethodInstance();
    }

    /**
     * Get method instance withot method
     *
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
     * Get method instance requested method
     *
     * @return void
     */
    public function testGetMethodInstanceRequestedMethod(): void
    {
        $code = 'real_method';
        $this->info->setData('method', $code);

        $this->paymentHelperMock->expects($this->once())
            ->method('getMethodInstance')->with($code)
            ->willReturn($this->methodInstanceMock);

        $this->methodInstanceMock->expects($this->once())
            ->method('setInfoInstance')
            ->with($this->info);

        $this->assertSame($this->methodInstanceMock, $this->info->getMethodInstance());

        // as the method is already stored at Info, check that it's not initialized again
        $this->assertSame($this->methodInstanceMock, $this->info->getMethodInstance());
    }

    /**
     * Encrypt test
     *
     * @return void
     */
    public function testEncrypt(): void
    {
        $data = 'data';
        $encryptedData = 'd1a2t3a4';

        $this->encryptorInterfaceMock->expects($this->once())
            ->method('encrypt')
            ->with($data)
            ->willReturn($encryptedData);

        $this->assertEquals($encryptedData, $this->info->encrypt($data));
    }

    /**
     * Decrypt test
     *
     * @return void
     */
    public function testDecrypt(): void
    {
        $data = 'data';
        $encryptedData = 'd1a2t3a4';

        $this->encryptorInterfaceMock->expects($this->once())
            ->method('decrypt')
            ->with($encryptedData)
            ->willReturn($data);

        $this->assertEquals($data, $this->info->decrypt($encryptedData));
    }

    /**
     * Set additional information exception
     *
     * @return void
     */
    public function testSetAdditionalInformationException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->info->setAdditionalInformation('object', new \stdClass());
    }

    /**
     * Set additional info multiple types
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     * @dataProvider additionalInformationDataProvider
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
    public function additionalInformationDataProvider(): array
    {
        return [
            [['key1' => 'data1', 'key2' => 'data2'], null],
            ['key', 'data']
        ];
    }

    /**
     * Get additional info by key
     *
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
     * Unsetter additional info
     *
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
     * Has additional info
     *
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
     * Init additional info with unserialize
     *
     * @return void
     */
    public function testInitAdditionalInformationWithUnserialize(): void
    {
        $data = ['key1' => 'data1', 'key2' => 'data2'];
        $this->info->setData('additional_information', $data);

        $this->assertEquals($data, $this->info->getAdditionalInformation());
    }
}
