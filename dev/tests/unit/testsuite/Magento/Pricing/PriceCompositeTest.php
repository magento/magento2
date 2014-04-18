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
 * @category    Magento
 * @package     Magento_Pricing
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Pricing;

use Magento\Pricing\Price\Factory as PriceFactory;
use Magento\Catalog\Pricing\Price\FinalPriceInterface;
use Magento\Catalog\Pricing\Price\GroupPriceInterface;
use Magento\Catalog\Pricing\Price\SpecialPriceInterface;

/**
 * Test class for \Magento\Pricing\PriceComposite
 */
class PriceCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceComposite
     */
    protected $model;

    /**
     * @var PriceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceFactory;

    /**
     * @var array
     */
    protected $metadata;

    public function setUp()
    {
        $this->priceFactory = $this->getMockBuilder('Magento\Pricing\Price\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadata = array(
            FinalPriceInterface::PRICE_TYPE_FINAL => ['class' => 'Class\For\FinalPrice'],
            GroupPriceInterface::PRICE_TYPE_GROUP => ['class' => 'Class\For\GroupPrice'],
            SpecialPriceInterface::PRICE_TYPE_SPECIAL => ['class' => 'Class\For\SpecialPrice']
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Pricing\PriceComposite', array(
            'priceFactory' => $this->priceFactory,
            'metadata' => $this->metadata
        ));
    }

    public function testGetPriceCodes()
    {
        $expectedCodes = [
            FinalPriceInterface::PRICE_TYPE_FINAL,
            GroupPriceInterface::PRICE_TYPE_GROUP,
            SpecialPriceInterface::PRICE_TYPE_SPECIAL
        ];
        $this->assertEquals($expectedCodes, $this->model->getPriceCodes());
    }

    public function testGetMetadata()
    {
        $this->assertEquals($this->metadata, $this->model->getMetadata());
    }

    public function testCreatePriceObject()
    {
        $saleable = $this->getMock('Magento\Pricing\Object\SaleableInterface');
        $priceCode = FinalPriceInterface::PRICE_TYPE_FINAL;
        $quantity = 2.4;

        $price = $this->getMock('Magento\Pricing\Price\PriceInterface');

        $this->priceFactory->expects($this->once())
            ->method('create')
            ->with($saleable, $this->metadata[$priceCode]['class'], $quantity)
            ->will($this->returnValue($price));

        $this->assertEquals($price, $this->model->createPriceObject($saleable, $priceCode, $quantity));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage wrong_price is not registered in prices list
     */
    public function testCreatePriceObjectWithException()
    {
        $saleable = $this->getMock('Magento\Pricing\Object\SaleableInterface');
        $priceCode = 'wrong_price';
        $quantity = 2.4;

        $this->model->createPriceObject($saleable, $priceCode, $quantity);
    }
}
