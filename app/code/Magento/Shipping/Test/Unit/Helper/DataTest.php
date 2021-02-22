<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Shipping\Helper\Data as HelperData;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Data helper test
 *
 * Class \Magento\Shipping\Test\Unit\Helper\DataTest
 */
class DataTest extends TestCase
{
    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @var DecoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlDecoderMock;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * Setup environment to test
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->urlDecoderMock = $this->getMockForAbstractClass(DecoderInterface::class);
        $this->contextMock->expects($this->any())->method('getUrlDecoder')
            ->willReturn($this->urlDecoderMock);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->helper = $this->objectManagerHelper->getObject(
            HelperData::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * test decodeTrackingHash() with data provider below
     *
     * @param string $hash
     * @param string $urlDecodeResult
     * @param array $expected
     * @dataProvider decodeTrackingHashDataProvider
     */
    public function testDecodeTrackingHash($hash, $urlDecodeResult, $expected)
    {
        $this->urlDecoderMock->expects($this->any())->method('decode')
            ->with($hash)
            ->willReturn($urlDecodeResult);
        $this->assertEquals($expected, $this->helper->decodeTrackingHash($hash));
    }

    /**
     * Dataset to test getData()
     *
     * @return array
     */
    public function decodeTrackingHashDataProvider()
    {
        return [
            'Test with hash key is allowed' => [
                strtr(base64_encode('order_id:1:protected_code'), '+/=', '-_,'),
                'order_id:1:protected_code',
                [
                    'key' => 'order_id',
                    'id' => 1,
                    'hash' => 'protected_code'
                ]
            ],
            'Test with hash key is not allowed' => [
                strtr(base64_encode('invoice_id:1:protected_code'), '+/=', '-_,'),
                'invoice_id:1:protected_code',
                []
            ]
        ];
    }
}
