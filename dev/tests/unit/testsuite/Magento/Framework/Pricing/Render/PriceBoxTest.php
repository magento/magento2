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
namespace Magento\Framework\Pricing\Render;

/**
 * Test class for \Magento\Framework\Pricing\Render\PriceBox
 */
class PriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var PriceBox
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Pricing\Render\RendererPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererPool;

    /**
     * @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleable;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $price;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->rendererPool = $this->getMockBuilder('Magento\Framework\Pricing\Render\RendererPool')
            ->disableOriginalConstructor()
            ->setMethods(['createAmountRender'])
            ->getMock();

        $layout = $this->getMock('Magento\Framework\View\LayoutInterface');
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $scopeConfigMock = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');
        $storeConfig = $this->getMockBuilder('Magento\Store\Model\Store\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $this->context->expects($this->any())
            ->method('getStoreConfig')
            ->will($this->returnValue($storeConfig));
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));

        $this->saleable = $this->getMock('Magento\Framework\Pricing\Object\SaleableInterface');

        $this->price = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');

        $this->model = $this->objectManager->getObject('Magento\Framework\Pricing\Render\PriceBox', array(
            'context' => $this->context,
            'saleableItem' => $this->saleable,
            'price' => $this->price,
            'rendererPool' => $this->rendererPool
        ));
    }

    /**
     * @param array $data
     * @param string $priceCode
     * @param array $cssClasses
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($data, $priceCode, $cssClasses)
    {
        $this->price->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $priceBox = $this->objectManager->getObject('Magento\Framework\Pricing\Render\PriceBox', array(
            'context' => $this->context,
            'saleableItem' => $this->saleable,
            'price' => $this->price,
            'rendererPool' => $this->rendererPool,
            'data' => $data
        ));
        $priceBox->toHtml();
        $this->assertEquals($cssClasses, $priceBox->getData('css_classes'));
    }

    public function toHtmlDataProvider()
    {
        return array(
            array(
                'data' => [],
                'price_code' => 'test_price',
                'css_classes' => 'price-test_price'
            ),
            array(
                'data' => ['css_classes' => 'some_css_class'],
                'price_code' => 'test_price',
                'css_classes' => 'some_css_class price-test_price'
        ));
    }

    public function testGetSaleableItem()
    {
        $this->assertEquals($this->saleable, $this->model->getSaleableItem());
    }

    public function testGetPrice()
    {
        $this->assertEquals($this->price, $this->model->getPrice());
    }

    public function testGetPriceType()
    {
        $priceCode = 'test_price';

        $price = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');

        $priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($priceCode)
            ->will($this->returnValue($price));

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));

        $this->assertEquals($price, $this->model->getPriceType($priceCode));
    }

    public function testRenderAmount()
    {
        $amount = $this->getMock('Magento\Framework\Pricing\Amount\AmountInterface');
        $arguments = [];
        $resultHtml = 'result_html';

        $amountRender = $this->getMockBuilder('Magento\Framework\Pricing\Render\Amount')
            ->disableOriginalConstructor()
            ->setMethods(['toHtml'])
            ->getMock();
        $amountRender->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($resultHtml));

        $this->rendererPool->expects($this->once())
            ->method('createAmountRender')
            ->with($amount, $this->saleable, $this->price, $arguments)
            ->will($this->returnValue($amountRender));

        $this->assertEquals($resultHtml, $this->model->renderAmount($amount, $arguments));
    }

    public function testGetPriceIdHasDataPriceId()
    {
        $priceId = 'data_price_id';
        $this->model->setData('price_id', $priceId);
        $this->assertEquals($priceId, $this->model->getPriceId());
    }

    /**
     * @dataProvider getPriceIdProvider
     * @param string $prefix
     * @param string $suffix
     * @param string $defaultPrefix
     * @param string $defaultSuffix
     */
    public function testGetPriceId($prefix, $suffix, $defaultPrefix, $defaultSuffix)
    {
        $priceId = 'price_id';
        $this->saleable->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($priceId));

        if (!empty($prefix)) {
            $this->model->setData('price_id_prefix', $prefix);
            $expectedPriceId = $prefix . $priceId;
        } else {
            $expectedPriceId = $defaultPrefix . $priceId;
        }
        if (!empty($suffix)) {
            $this->model->setData('price_id_suffix', $suffix);
            $expectedPriceId = $expectedPriceId . $suffix;
        } else {
            $expectedPriceId = $expectedPriceId . $defaultSuffix;
        }

        $this->assertEquals($expectedPriceId, $this->model->getPriceId($defaultPrefix, $defaultSuffix));
    }

    public function getPriceIdProvider()
    {
        return [
            ['prefix', 'suffix', 'default_prefix', 'default_suffix'],
            ['prefix', 'suffix', 'default_prefix', ''],
            ['prefix', 'suffix', '', 'default_suffix'],
            ['prefix', '', 'default_prefix', 'default_suffix'],
            ['', 'suffix', 'default_prefix', 'default_suffix'],
            ['', '', 'default_prefix', 'default_suffix'],
            ['prefix', 'suffix', '', '']
        ];
    }

    public function testGetRendererPool()
    {
        $this->assertEquals($this->rendererPool, $this->model->getRendererPool());
    }
}
