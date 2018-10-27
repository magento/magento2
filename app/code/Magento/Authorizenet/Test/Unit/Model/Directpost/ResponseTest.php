<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model\Directpost;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Authorizenet\Model\Directpost;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response
     */
    protected $responseModel;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->responseModel = $objectManager->getObject(\Magento\Authorizenet\Model\Directpost\Response::class);
    }

    /**
     * @param string $merchantMd5
     * @param string $merchantApiLogin
     * @param float|null $amount
     * @param float|string $amountTestFunc
     * @param string $transactionId
     * @dataProvider generateHashDataProvider
     */
    public function testGenerateHash($merchantMd5, $merchantApiLogin, $amount, $amountTestFunc, $transactionId)
    {
        $this->assertEquals(
            $this->generateHash($merchantMd5, $merchantApiLogin, $amountTestFunc, $transactionId),
            $this->responseModel->generateHash($merchantMd5, $merchantApiLogin, $amount, $transactionId)
        );
    }

    /**
     * @return array
     */
    public function generateHashDataProvider()
    {
        return [
            [
                'merchantMd5' => 'FCD7F001E9274FDEFB14BFF91C799306',
                'merchantApiLogin' => 'Magento',
                'amount' => null,
                'amountTestFunc' => '0.00',
                'transactionId' => '1'
            ],
            [
                'merchantMd5' => '8AEF4E508261A287C3E2F544720FCA3A',
                'merchantApiLogin' => 'Magento2',
                'amount' => 100.50,
                'amountTestFunc' => 100.50,
                'transactionId' => '2'
            ]
        ];
    }

    /**
     * @param $merchantMd5
     * @param $merchantApiLogin
     * @param $amount
     * @param $transactionId
     *
     * @return string
     */
    protected function generateHash($merchantMd5, $merchantApiLogin, $amount, $transactionId)
    {
        return strtoupper(md5($merchantMd5 . $merchantApiLogin . $transactionId . $amount));
    }

    /**
     * @param string $merchantMd5
     * @param string $merchantApiLogin
     * @param float|null $amount
     * @param string $transactionId
     * @param string $hash
     * @param bool $expectedValue
     * @dataProvider isValidHashDataProvider
     */
    public function testIsValidHash($merchantMd5, $merchantApiLogin, $amount, $transactionId, $hash, $expectedValue)
    {
        $this->responseModel->setXAmount($amount);
        $this->responseModel->setXTransId($transactionId);
        $this->responseModel->setData('x_MD5_Hash', $hash);
        $this->assertEquals($expectedValue, $this->responseModel->isValidHash($merchantMd5, $merchantApiLogin));
    }

    /**
     * @return array
     */
    public function isValidHashDataProvider()
    {
        return [
            [
                'merchantMd5' => 'FCD7F001E9274FDEFB14BFF91C799306',
                'merchantApiLogin' => 'Magento',
                'amount' => null,
                'transactionId' => '1',
                'hash' => '1F24A4EC9A169B2B2A072A5F168E16DC',
                'expectedValue' => true
            ],
            [
                'merchantMd5' => '8AEF4E508261A287C3E2F544720FCA3A',
                'merchantApiLogin' => 'Magento2',
                'amount' => 100.50,
                'transactionId' => '2',
                'hash' => '1F24A4EC9A169B2B2A072A5F168E16DC',
                'expectedValue' => false
            ]
        ];
    }

    /**
     * @param int $xResponseCode
     * @param bool $expectedValue
     * @dataProvider isApprovedDataProvider
     */
    public function testIsApproved($xResponseCode, $expectedValue)
    {
        $this->responseModel->setXResponseCode($xResponseCode);
        $this->assertSame($expectedValue, $this->responseModel->isApproved());
    }

    /**
     * @return array
     */
    public function isApprovedDataProvider()
    {
        return [
            [Directpost::RESPONSE_CODE_APPROVED, true],
            [Directpost::RESPONSE_CODE_DECLINED, false],
            [Directpost::RESPONSE_CODE_ERROR, false],
            [Directpost::RESPONSE_CODE_HELD, false],
        ];
    }
}
