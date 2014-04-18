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
namespace Magento\Pricing\Render;

use Magento\Pricing\Object\SaleableInterface;
use Magento\Pricing\Price\PriceInterface;

/**
 * Test class for \Magento\Pricing\Render\Amount
 */
class AmountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Amount
     */
    protected $model;

    /**
     * @var \Magento\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var RendererPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererPool;

    /**
     * @var \Magento\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var PriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceMock;

    public function setUp()
    {
        $this->priceCurrency = $this->getMock('Magento\Pricing\PriceCurrencyInterface');
        $data = [
            'default' => [
                'adjustments' => [
                    'base_price_test' => [
                        'tax' => [
                            'adjustment_render_class' => 'Magento\View\Element\Template',
                            'adjustment_render_template' => 'template.phtml'
                        ]
                    ]
                ]
            ]
        ];

        $this->rendererPool = $this->getMock(
            'Magento\Pricing\Render\RendererPool',
            [],
            ['data' => $data],
            '',
            false,
            false
        );

        $this->layout = $this->getMock('Magento\View\Layout', [], [], '', false);

        $eventManager = $this->getMock('Magento\Event\ManagerStub', [], [], '', false);
        $config = $this->getMock('Magento\Store\Model\Store\Config', [], [], '', false);
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
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));


        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Pricing\Render\Amount',
            [
                'context' => $context,
                'priceCurrency' => $this->priceCurrency,
                'rendererPool' => $this->rendererPool
            ]
        );
    }

    public function testConvertAndFormatCurrency()
    {
        $amount = '100';
        $includeContainer = true;
        $precision = \Magento\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION;

        $result = '100.0 grn';

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($amount, $includeContainer, $precision)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->convertAndFormatCurrency($amount, $includeContainer, $precision));
    }

    /**
     * Test case for getAdjustmentRenders method through toHtml()
     */
    public function testToHtmlGetAdjustmentRenders()
    {
        $adjustmentRender = [];
        $this->rendererPool->expects($this->once())
            ->method('getAdjustmentRenders')
            ->will($this->returnValue($adjustmentRender));

        $this->model->toHtml();
    }
}
