<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Setup;

class TaxSetupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Setup\TaxSetup
     */
    protected $taxSetup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeConfigMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->typeConfigMock = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->taxSetup = $helper->getObject(
            'Magento\Tax\Setup\TaxSetup',
            ['productTypeConfig' => $this->typeConfigMock]
        );
    }

    public function testGetTaxableItems()
    {
        $refundable = ['simple', 'simple2'];
        $this->typeConfigMock->expects(
            $this->once()
        )->method(
            'filter'
        )->with(
            'taxable'
        )->will(
            $this->returnValue($refundable)
        );
        $this->assertEquals($refundable, $this->taxSetup->getTaxableItems());
    }
}
