<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Block\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Block\Grid\Renderer\Codes;

/**
 * Test for Tax Rates codes column of Tax Rules grid.
 *
 * Class CodesTest
 */
class CodesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Codes
     */
    private $codes;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->codes = $objectManager->getObject(Codes::class);
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
    public function ratesCodesDataProvider()
    {
        return [
            [['some_code'], 'some_code'],
            [['some_code', 'some_code2'], 'some_code, some_code2'],
            [[], ''],
            [null, '']
        ];
    }
}
