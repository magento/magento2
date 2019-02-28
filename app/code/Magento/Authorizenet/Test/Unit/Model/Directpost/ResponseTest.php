<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model\Directpost;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Authorizenet\Model\Directpost;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseModelMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->responseModelMock = $objectManager->getObject(\Magento\Authorizenet\Model\Directpost\Response::class);
    }

    /**
     * @param $merchantMd5
     * @param $merchantApiLogin
     * @param $amount
     * @param $transactionId
     * @return string
     */
    protected function generateHash($merchantMd5, $merchantApiLogin, $amount, $transactionId)
    {
        return strtoupper(md5($merchantMd5 . $merchantApiLogin . $transactionId . $amount));
    }

    /**
     * @param string $storedHash
     * @param string $hashKey
     * @param string $merchantApiLogin
     * @param float|null $amount
     * @param string $transactionId
     * @param string $hash
     * @param bool $expectedValue
     * @return void
     * @dataProvider isValidHashDataProvider
     */
    public function testIsValidHash(
        $storedHash,
        $hashKey,
        $merchantApiLogin,
        $amount,
        $transactionId,
        $hash,
        $expectedValue
    ) {
        $this->responseModelMock->setXAmount($amount);
        $this->responseModelMock->setXTransId($transactionId);
        $this->responseModelMock->setData($hashKey, $hash);
        $result = $this->responseModelMock->isValidHash($storedHash, $merchantApiLogin);

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * @return array
     */
    public function isValidHashDataProvider()
    {
        $signatureKey = '3EAFCE5697C1B4B9748385C1FCD29D86F3B9B41C7EED85A3A01DFF6570C8C' .
            '29373C2A153355C3313CDF4AF723C0036DBF244A0821713A910024EE85547CEF37F';
        $expectedSha2Hash = '368D48E0CD1274BF41C059138DA69985594021A4AD5B4C5526AE88C8F' .
            '7C5769B13C5E1E4358900F3E51076FB69D14B0A797904C22E8A11A52AA49CDE5FBB703C';
        return [
            [
                'merchantMd5' => 'FCD7F001E9274FDEFB14BFF91C799306',
                'hashKey' => 'x_MD5_Hash',
                'merchantApiLogin' => 'Magento',
                'amount' => null,
                'transactionId' => '1',
                'hash' => '1F24A4EC9A169B2B2A072A5F168E16DC',
                'expectedValue' => true
            ],
            [
                'merchantMd5' => '8AEF4E508261A287C3E2F544720FCA3A',
                'hashKey' => 'x_MD5_Hash',
                'merchantApiLogin' => 'Magento2',
                'amount' => 100.50,
                'transactionId' => '2',
                'hash' => '1F24A4EC9A169B2B2A072A5F168E16DC',
                'expectedValue' => false
            ],
            [
                'signatureKey' => $signatureKey,
                'hashKey' => 'x_SHA2_Hash',
                'merchantApiLogin' => 'Magento2',
                'amount' => 100.50,
                'transactionId' => '2',
                'hash' => $expectedSha2Hash,
                'expectedValue' => true
            ]
        ];
    }

    /**
     * @param int $xResponseCode
     * @param bool $expectedValue
     * @return void
     * @dataProvider isApprovedDataProvider
     */
    public function testIsApproved($xResponseCode, $expectedValue)
    {
        $this->responseModelMock->setXResponseCode($xResponseCode);
        $this->assertSame($expectedValue, $this->responseModelMock->isApproved());
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
