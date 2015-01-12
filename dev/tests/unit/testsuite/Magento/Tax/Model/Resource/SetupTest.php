<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Resource;

class SetupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Resource\Setup
     */
    protected $taxSetup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeConfigMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->typeConfigMock = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->taxSetup = $helper->getObject(
            'Magento\Tax\Model\Resource\Setup',
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
