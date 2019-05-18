<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Rss\Signature;

/**
 * Test signature class.
 */
class SignatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\DeploymentConfig
     */
    private $encryptorMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Sales\Model\Rss\Signature
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Signature::class,
            [
                'encryptor' => $this->encryptorMock,
            ]
        );
    }

    /**
     * Test sign data.
     *
     * @param string $data
     * @param string $expected
     * @return void
     * @dataProvider checkSignatureDataProvider
     */
    public function testSignData($data, $expected)
    {
        $this->encryptorMock->expects($this->any())->method('hash')->with($data)->willReturn($expected);
        $this->assertEquals($expected, $this->model->signData($data));
    }

    /**
     * @return array
     */
    public function checkSignatureDataProvider()
    {
        return [
            [
                'eyJvcmRlcl9pZCI6IjEiLCJjdXN0b21lcl9pZCI6IjEiLCJpbmNyZW1lbnRfaWQiOiIwMDAwMDAwMDEifQ==',
                '651932dfc862406b72628d95623bae5ea18242be757b3493b337942d61f834be',
            ],
        ];
    }

    /**
     * Test signature validation.
     *
     * @param string $data
     * @param string $signature
     * @param bool $expected
     * @return void
     * @dataProvider checkIsValidDataProvider
     */
    public function testIsValid($data, $signature, $expected)
    {
        $this->encryptorMock->expects($this->any())
            ->method('validateHash')
            ->with($data, $signature)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->isValid($data, $signature));
    }

    /**
     * @return array
     */
    public function checkIsValidDataProvider()
    {
        return [
            [
                'eyJvcmRlcl9pZCI6IjEiLCJjdXN0b21lcl9pZCI6IjEiLCJpbmNyZW1lbnRfaWQiOiIwMDAwMDAwMDEifQ==',
                '651932dfc862406b72628d95623bae5ea18242be757b3493b337942d61f834be',
                true,
            ],
            [
                'eyJvcmRlcl9pZCI6IjEiLCJjdXN0b21lcl9pZCI6IjEiLCJpbmNyZW1lbnRfaWQiOiIwMDAwMDAwMDEifQ==',
                'blabla',
                false,
            ],
        ];
    }
}
