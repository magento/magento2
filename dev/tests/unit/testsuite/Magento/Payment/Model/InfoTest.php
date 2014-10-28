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

namespace Magento\Payment\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Info */
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

    protected function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $this->registryMock = $this->getMock('Magento\Framework\Registry');
        $this->paymentHelperMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $this->encryptorInterfaceMock = $this->getMock(
            'Magento\Framework\Encryption\EncryptorInterface',
            [],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->info = $this->objectManagerHelper->getObject(
            'Magento\Payment\Model\Info',
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

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testGetMethodInstanceException()
    {
        $this->info->getMethodInstance();
    }

    public function testGetMethodInstanceSubstitution()
    {
        $code = 'unreal_method';
        $this->info->setData('method', $code);

        $methodInstance = $this->getMockBuilder(
            'Magento\Payment\Model\MethodInterface')->disableOriginalConstructor()->setMethods(
                ['setInfoInstance', 'getCode', 'getFormBlockType', 'getTitle']
            )->getMock();
        $this->paymentHelperMock->expects($this->at(0))->method('getMethodInstance')->with($code)->will(
            $this->returnValue(null)
        );
        $this->paymentHelperMock->expects($this->at(1))->method('getMethodInstance')->with(
            Method\Substitution::CODE
        )->will($this->returnValue($methodInstance));

        $methodInstance->expects($this->once())->method('setInfoInstance')->with($this->info);
        $this->assertSame($methodInstance, $this->info->getMethodInstance());
    }

    public function testGetMethodInstanceRequestedMethod()
    {
        $code = 'unreal_method';
        $this->info->setData('method', $code);

        $methodInstance = $this->getMockBuilder(
            'Magento\Payment\Model\MethodInterface')->disableOriginalConstructor()->setMethods(
                ['setInfoInstance', 'getCode', 'getFormBlockType', 'getTitle']
            )->getMock();
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($code)->will(
            $this->returnValue($methodInstance)
        );

        $methodInstance->expects($this->once())->method('setInfoInstance')->with($this->info);
        $this->assertSame($methodInstance, $this->info->getMethodInstance());

        // as the method is already stored at Info, check that it's not initialized again
        $this->assertSame($methodInstance, $this->info->getMethodInstance());
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
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testSetAdditionalInformationException()
    {
        $this->info->setAdditionalInformation('object', new \StdClass);
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
}
