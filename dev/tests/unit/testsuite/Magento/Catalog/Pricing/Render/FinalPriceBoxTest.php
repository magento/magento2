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

namespace Magento\Catalog\Pricing\Render;

/**
 * Class FinalPriceBoxTest
 */
class FinalPriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Render\FinalPriceBox
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceBox;

    /**
     * @var \Magento\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Pricing\Render\RendererPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererPool;

    /**
     * @var \Magento\Pricing\Price\PriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $price;

    protected function setUp()
    {
        $this->product = $this->getMockForAbstractClass(
            'Magento\Pricing\Object\SaleableInterface',
            [],
            '',
            true,
            true,
            true,
            ['getPriceInfo', '__wakeup']
        );

        $this->priceType = $this->getMockBuilder('Magento\Catalog\Pricing\Price\MsrpPrice')
            ->disableOriginalConstructor()
            ->setMethods(['isShowPriceOnGesture', 'getMsrpPriceMessage', 'canApplyMsrp'])
            ->getMock();

        $this->priceInfo = $this->getMockBuilder('Magento\Pricing\PriceInfo')
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();

        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $eventManager = $this->getMock('Magento\Event\ManagerStub', [], [], '', false);
        $config = $this->getMock('Magento\Store\Model\Store\Config', [], [], '', false);
        $this->layout = $this->getMock('Magento\View\Layout', [], [], '', false);

        $this->priceBox = $this->getMock('Magento\Pricing\Render\PriceBox', [], [], '', false);
        $this->logger = $this->getMock('Magento\Logger', [], [], '', false);


        $this->layout->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValue($this->priceBox));

        $scopeConfigMock = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');
        $context = $this->getMock('Magento\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $context->expects($this->any())
            ->method('getStoreConfig')
            ->will($this->returnValue($config));
        $context->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $context->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($this->logger));
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));

        $this->rendererPool = $this->getMockBuilder('Magento\Pricing\Render\RendererPool')
            ->disableOriginalConstructor()
            ->getMock();

        $this->price = $this->getMock('Magento\Pricing\Price\PriceInterface');
        $this->price->expects($this->any())
            ->method('getPriceType')
            ->will($this->returnValue(\Magento\Catalog\Pricing\Price\FinalPriceInterface::PRICE_TYPE_FINAL));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            'Magento\Catalog\Pricing\Render\FinalPriceBox',
            array(
                'context' => $context,
                'saleableItem' => $this->product,
                'rendererPool' => $this->rendererPool,
                'price' => $this->price
            )
        );
    }

    public function testRenderMsrpDisabled()
    {
        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo('msrp_price'))
            ->will($this->returnValue($this->priceType));

        $this->priceType->expects($this->any())
            ->method('canApplyMsrp')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue(false));

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertRegExp('/[final_price]/', $result);
    }

    public function testRenderMsrpEnabled()
    {
        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo('msrp_price'))
            ->will($this->returnValue($this->priceType));

        $this->priceType->expects($this->any())
            ->method('canApplyMsrp')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue(true));

        $priceBoxRender = $this->getMockBuilder('Magento\Pricing\Render\PriceBox')
            ->disableOriginalConstructor()
            ->getMock();
        $priceBoxRender->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue('test'));

        $this->rendererPool->expects($this->once())
            ->method('createPriceRender')
            ->with('msrp_price')
            ->will($this->returnValue($priceBoxRender));

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertEquals('<div class="price-box price-final_price">test</div>', $result);
    }

    public function testRenderMsrpNotRegisteredException()
    {
        $this->logger->expects($this->once())
            ->method('logException');

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo('msrp_price'))
            ->will($this->throwException(new \InvalidArgumentException()));

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertRegExp('/[final_price]/', $result);
    }
}
