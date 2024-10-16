<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Block\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Block\Grid\Renderer\Codes;
use PHPUnit\Framework\TestCase;

/**
 * Test for Tax Rates codes column of Tax Rules grid.
 *
 * Class CodesTest
 */
class CodesTest extends TestCase
{
    /**
     * @var Codes
     */
    private $codes;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnCallback(
                function ($str) {
                    return 'ESCAPED:' . $str;
                }
            );
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getEscaper')
            ->willReturn($escaper);
        $this->codes = $objectManager->getObject(
            Codes::class,
            ['context' => $context]
        );
    }

    /**
     * Test rates codes grid column renderer.
     *
     * @param array $ratesCodes
     * @param string $expected
     * @see Magento\Tax\Block\Grid\Renderer\Codes::render
     * @dataProvider ratesCodesDataProvider
     */
    public function testRenderCodes($ratesCodes, $expected)
    {
        $row = new DataObject();
        $row->setTaxRatesCodes($ratesCodes);
        $this->assertEquals($expected, $this->codes->render($row));
    }

    /**
     * Provider of rates codes and render expectations.
     *
     * @return array
     */
    public static function ratesCodesDataProvider()
    {
        return [
            [['some_code'], 'ESCAPED:some_code'],
            [['some_code', 'some_code2'], 'ESCAPED:some_code, some_code2'],
            [[], ''],
            [null, ''],
        ];
    }
}
