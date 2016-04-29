<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Model\Method;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\InfoInterface */
    protected $info;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Payment\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentHelperMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorInterfaceMock;

    /** @var \Magento\Payment\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $methodInstanceMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(\Magento\Framework\Model\Context::class, [], [], '', false);
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class);
        $this->paymentHelperMock = $this->getMock(
            \Magento\Payment\Helper\Data::class,
            ['getMethodInstance'],
            [],
            '',
            false
        );
        $this->encryptorInterfaceMock = $this->getMock(
            \Magento\Framework\Encryption\EncryptorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->info = $this->objectManagerHelper->getObject(
            \Magento\Payment\Model\Info::class,
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
     */
    public function testGetDataCcNumber($keyCc, $keyCcEnc)
    {
        // no data was set
        $this->assertNull($this->info->getData($keyCc));

        // we set encrypted data
        $this->info->setData($keyCcEnc, $keyCcEnc);
        $this->encryptorInterfaceMock->expects($this->once())->method('decrypt')->with($keyCcEnc)->will(
            $this->returnValue($keyCc)
        );
        $this->assertEquals($keyCc, $this->info->getData($keyCc));
    }

    /**
     * Returns array of Cc keys which needs prepare logic
     *
     * @return array
     */
    public function ccKeysDataProvider()
    {
        return [
            ['cc_number', 'cc_number_enc'],
            ['cc_cid', 'cc_cid_enc']
        ];
    }

    public function testGetMethodInstanceWithRealMethod()
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

    public function testGetMethodInstanceWithUnrealMethod()
    {
        $method = 'unreal_method';
        $this->info->setData('method', $method);

        $this->paymentHelperMock->expects($this->at(0))
            ->method('getMethodInstance')
            ->with($method)
            ->willThrowException(new \UnexpectedValueException());

        $this->methodInstanceMock->expects($this->once())
            ->method('setInfoInstance')
            ->with($this->info);

        $this->paymentHelperMock->expects($this->at(1))
            ->method('getMethodInstance')
            ->with(Method\Substitution::CODE)
            ->willReturn($this->methodInstanceMock);

        $this->info->getMethodInstance();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The payment method you requested is not available.
     */
    public function testGetMethodInstanceWithNoMethod()
    {
        $this->info->setData('method', false);
        $this->info->getMethodInstance();
    }

    public function testGetMethodInstanceRequestedMethod()
    {
        $code = 'real_method';
        $this->info->setData('method', $code);

        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($code)->will(
            $this->returnValue($this->methodInstanceMock)
        );

        $this->methodInstanceMock->expects($this->once())->method('setInfoInstance')->with($this->info);
        $this->assertSame($this->methodInstanceMock, $this->info->getMethodInstance());

        // as the method is already stored at Info, check that it's not initialized again
        $this->assertSame($this->methodInstanceMock, $this->info->getMethodInstance());
    }

    public function testEncrypt()
    {
        $data = 'data';
        $encryptedData = 'd1a2t3a4';

        $this->encryptorInterfaceMock->expects($this->once())->method('encrypt')->with($data)->will(
            $this->returnValue($encryptedData)
        );
        $this->assertEquals($encryptedData, $this->info->encrypt($data));
    }

    public function testDecrypt()
    {
        $data = 'data';
        $encryptedData = 'd1a2t3a4';

        $this->encryptorInterfaceMock->expects($this->once())->method('decrypt')->with($encryptedData)->will(
            $this->returnValue($data)
        );
        $this->assertEquals($data, $this->info->decrypt($encryptedData));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testSetAdditionalInformationException()
    {
        $this->info->setAdditionalInformation('object', new \StdClass());
    }

    /**
     * @dataProvider additionalInformationDataProvider
     * @param mixed $key
     * @param mixed $value
     */
    public function testSetAdditionalInformationMultipleTypes($key, $value = null)
    {
        $this->info->setAdditionalInformation($key, $value);
        $this->assertEquals($value ? [$key => $value] : $key, $this->info->getAdditionalInformation());
    }

    /**
     * Prepared data for testSetAdditionalInformationMultipleTypes
     *
     * @return array
     */
    public function additionalInformationDataProvider()
    {
        return [
            [['key1' => 'data1', 'key2' => 'data2'], null],
            ['key', 'data']
        ];
    }

    public function testGetAdditionalInformationByKey()
    {
        $key = 'key';
        $value = 'value';
        $this->info->setAdditionalInformation($key, $value);
        $this->assertEquals($value, $this->info->getAdditionalInformation($key));
    }

    public function testUnsAdditionalInformation()
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

    public function testHasAdditionalInformation()
    {
        $this->assertFalse($this->info->hasAdditionalInformation());

        $data = ['key1' => 'data1', 'key2' => 'data2'];
        $this->info->setAdditionalInformation($data);
        $this->assertFalse($this->info->hasAdditionalInformation('key3'));

        $this->assertTrue($this->info->hasAdditionalInformation('key2'));
        $this->assertTrue($this->info->hasAdditionalInformation());
    }

    public function testInitAdditionalInformationWithUnserialize()
    {
        $data = ['key1' => 'data1', 'key2' => 'data2'];
        $this->info->setData('additional_information', $data);

        $this->assertEquals($data, $this->info->getAdditionalInformation());
    }
}
