<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Render;

use Magento\Framework\Pricing\Render\RendererPool;

/**
 * Test class for \Magento\Framework\Pricing\Render\RendererPool
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Render\RendererPool | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $object;

    /**
     * @var \Magento\Framework\View\Layout | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Catalog\Model\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\BasePrice | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    protected function setUp()
    {
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));
        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceMock = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\BasePrice::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test createPriceRender() if not found render class name
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class name for price code "price_test" not registered
     */
    public function testCreatePriceRenderNoClassName()
    {
        $methodData = [];
        $priceCode = 'price_test';
        $data = [];
        $type = 'simple';
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));

        $testedClass = $this->createTestedEntity($data);
        $result = $testedClass->createPriceRender($priceCode, $this->productMock, $methodData);
        $this->assertNull($result);
    }

    /**
     * Test createPriceRender() if not found price model
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Price model for price code "price_test" not registered
     */
    public function testCreatePriceRenderNoPriceModel()
    {
        $methodData = [];
        $priceCode = 'price_test';
        $type = 'simple';
        $className = 'Test';
        $data = [
            $type => [
                'prices' => [
                    $priceCode => [
                        'render_class' => $className,
                    ],
                ],
            ],
        ];
        $priceModel = null;

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo($priceCode))
            ->will($this->returnValue($priceModel));
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $testedClass = $this->createTestedEntity($data);
        $result = $testedClass->createPriceRender($priceCode, $this->productMock, $methodData);
        $this->assertNull($result);
    }

    /**
     * Test createPriceRender() if not found price model
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Block "Magento\Framework\View\Element\Template\Context" must implement
     * \Magento\Framework\Pricing\Render\PriceBoxRenderInterface
     */
    public function testCreatePriceRenderBlockNotPriceBox()
    {
        $methodData = [];
        $priceCode = 'price_test';
        $type = 'simple';
        $className = \Magento\Framework\View\Element\Template\Context::class;
        $data = [
            $type => [
                'prices' => [
                    $priceCode => [
                        'render_class' => $className,
                    ],
                ],
            ],
        ];

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo($priceCode))
            ->will($this->returnValue($this->priceMock));
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $block = new \Magento\Framework\View\Element\Template($contextMock);

        $testedClass = $this->createTestedEntity($data);

        $arguments = [
            'data' => $methodData,
            'rendererPool' => $testedClass,
            'price' => $this->priceMock,
            'saleableItem' => $this->productMock,
        ];
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($className), $this->equalTo(''), $this->equalTo($arguments))
            ->will($this->returnValue($block));

        $result = $testedClass->createPriceRender($priceCode, $this->productMock, $methodData);
        $this->assertNull($result);
    }

    /**
     * Test createPriceRender()
     */
    public function testCreatePriceRender()
    {
        $methodData = [];
        $priceCode = 'price_test';
        $type = 'simple';
        $className = \Magento\Framework\View\Element\Template\Context::class;
        $template = 'template.phtml';
        $data = [
            $type => [
                'prices' => [
                    $priceCode => [
                        'render_class' => $className,
                        'render_template' => $template,
                    ],
                ],
            ],
        ];

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo($priceCode))
            ->will($this->returnValue($this->priceMock));
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $renderBlock = $this->getMockBuilder(\Magento\Framework\Pricing\Render\PriceBox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderBlock->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo($template));

        $testedClass = $this->createTestedEntity($data);

        $arguments = [
            'data' => $methodData,
            'rendererPool' => $testedClass,
            'price' => $this->priceMock,
            'saleableItem' => $this->productMock,
        ];
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($className), $this->equalTo(''), $this->equalTo($arguments))
            ->will($this->returnValue($renderBlock));

        $result = $testedClass->createPriceRender($priceCode, $this->productMock, $methodData);
        $this->assertInstanceOf(\Magento\Framework\Pricing\Render\PriceBoxRenderInterface::class, $result);
    }

    /**
     * Test createAmountRender() if amount render class not found
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage There is no amount render class for price code "base_price_test"
     */
    public function testCreateAmountRenderNoAmountClass()
    {
        $data = [];
        $type = 'simple';
        $methodData = [];
        $priceCode = 'base_price_test';

        $amountMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->priceMock->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $testedClass = $this->createTestedEntity($data);
        $result = $testedClass->createAmountRender($amountMock, $this->productMock, $this->priceMock, $methodData);
        $this->assertNull($result);
    }

    /**
     * Test createAmountRender() if amount render block not implement Amount interface
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Block "Magento\Framework\View\Element\Template\Context"
     * must implement \Magento\Framework\Pricing\Render\AmountRenderInterface
     */
    public function testCreateAmountRenderNotAmountInterface()
    {
        $type = 'simple';
        $methodData = [];
        $priceCode = 'base_price_test';
        $amountRenderClass = \Magento\Framework\View\Element\Template\Context::class;
        $data = [
            $type => [
                'prices' => [
                    $priceCode => [
                        'amount_render_class' => $amountRenderClass,
                    ],
                ],
            ],
        ];

        $amountMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->priceMock->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $block = new \Magento\Framework\View\Element\Template($contextMock);

        $testedClass = $this->createTestedEntity($data);

        $arguments = [
            'data' => $methodData,
            'rendererPool' => $testedClass,
            'amount' => $amountMock,
            'saleableItem' => $this->productMock,
            'price' => $this->priceMock,
        ];

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($amountRenderClass), $this->equalTo(''), $this->equalTo($arguments))
            ->will($this->returnValue($block));

        $result = $testedClass->createAmountRender($amountMock, $this->productMock, $this->priceMock, $methodData);
        $this->assertNull($result);
    }

    /**
     * Test createAmountRender()
     */
    public function testCreateAmountRender()
    {
        $type = 'simple';
        $methodData = [];
        $priceCode = 'base_price_test';
        $template = 'template.phtml';
        $amountRenderClass = \Magento\Framework\Pricing\Render\Amount::class;
        $data = [
            $type => [
                'prices' => [
                    $priceCode => [
                        'amount_render_class' => $amountRenderClass,
                        'amount_render_template' => $template,
                    ],
                ],
            ],
        ];

        $amountMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->priceMock->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $blockMock = $this->getMockBuilder(\Magento\Framework\Pricing\Render\Amount::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testedClass = $this->createTestedEntity($data);

        $arguments = [
            'data' => $methodData,
            'rendererPool' => $testedClass,
            'amount' => $amountMock,
            'saleableItem' => $this->productMock,
            'price' => $this->priceMock,
        ];

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($amountRenderClass), $this->equalTo(''), $this->equalTo($arguments))
            ->will($this->returnValue($blockMock));

        $blockMock->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo($template));

        $result = $testedClass->createAmountRender($amountMock, $this->productMock, $this->priceMock, $methodData);
        $this->assertInstanceOf(\Magento\Framework\Pricing\Render\AmountRenderInterface::class, $result);
    }

    /**
     * Test getAdjustmentRenders() with not existed adjustment render class
     */
    public function testGetAdjustmentRendersNoRenderClass()
    {
        $typeId = 'simple';
        $priceCode = 'base_price_test';
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($typeId));
        $this->priceMock->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $code = 'test_code';
        $adjustments = [$code => 'some data'];
        $data = [
            'default' => [
                'adjustments' => $adjustments,
            ],
        ];
        $testedClass = $this->createTestedEntity($data);
        $result = $testedClass->getAdjustmentRenders($this->productMock, $this->priceMock);
        $this->assertNull($result);
    }

    /**
     * Test getAdjustmentRenders() with not existed adjustment render template
     */
    public function testGetAdjustmentRendersNoRenderTemplate()
    {
        $typeId = 'simple';
        $priceCode = 'base_price_test';
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($typeId));
        $this->priceMock->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $code = 'test_code';
        $adjustments = [
            $code => [
                'adjustment_render_class' => 'Test',
            ],
        ];
        $data = [
            'default' => [
                'adjustments' => $adjustments,
            ],
        ];

        $testedClass = $this->createTestedEntity($data);
        $result = $testedClass->getAdjustmentRenders($this->productMock, $this->priceMock);
        $this->assertNull($result);
    }

    /**
     * Test getAdjustmentRenders()
     */
    public function testGetAdjustmentRenders()
    {
        $typeId = 'simple';
        $priceCode = 'base_price_test';
        $class = \Magento\Framework\View\Element\Template::class;
        $template = 'template.phtml';

        $code = 'tax';
        $adjustments = [
            $priceCode => [
                $code => [
                    'adjustment_render_class' => $class,
                    'adjustment_render_template' => $template,
                ],
            ],
        ];
        $data = [
            'default' => [
                'adjustments' => $adjustments,
            ],
        ];

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($typeId));
        $this->priceMock->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo($template));

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($class))
            ->will($this->returnValue($blockMock));

        $testedClass = $this->createTestedEntity($data);
        $result = $testedClass->getAdjustmentRenders($this->productMock, $this->priceMock);
        $this->assertArrayHasKey($code, $result);
        $this->assertInstanceOf(\Magento\Framework\View\Element\Template::class, $result[$code]);
    }

    /**
     * Test getAmountRenderBlockTemplate() through createAmountRender() in case when template not exists
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage For type "simple" amount render block not configured
     */
    public function testGetAmountRenderBlockTemplateNoTemplate()
    {
        $type = 'simple';
        $methodData = [];
        $priceCode = 'base_price_test';
        $template = false;
        $amountRenderClass = \Magento\Framework\Pricing\Render\Amount::class;
        $data = [
            $type => [
                'prices' => [
                    $priceCode => [
                        'amount_render_class' => $amountRenderClass,
                        'amount_render_template' => $template,
                    ],
                ],
            ],
        ];

        $amountMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->priceMock->expects($this->once())
            ->method('getPriceCode')
            ->will($this->returnValue($priceCode));

        $blockMock = $this->getMockBuilder(\Magento\Framework\Pricing\Render\Amount::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testedClass = $this->createTestedEntity($data);

        $arguments = [
            'data' => $methodData,
            'rendererPool' => $testedClass,
            'amount' => $amountMock,
            'saleableItem' => $this->productMock,
            'price' => $this->priceMock,
        ];

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($amountRenderClass), $this->equalTo(''), $this->equalTo($arguments))
            ->will($this->returnValue($blockMock));

        $result = $testedClass->createAmountRender($amountMock, $this->productMock, $this->priceMock, $methodData);
        $this->assertNull($result);
    }

    /**
     * Test getRenderBlockTemplate() through createPriceRender() in case when template not exists
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Price code "price_test" render block not configured
     */
    public function testGetRenderBlockTemplate()
    {
        $methodData = [];
        $priceCode = 'price_test';
        $type = 'simple';
        $className = \Magento\Framework\View\Element\Template\Context::class;
        $template = false;
        $data = [
            $type => [
                'prices' => [
                    $priceCode => [
                        'render_class' => $className,
                        'render_template' => $template,
                    ],
                ],
            ],
        ];

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo($priceCode))
            ->will($this->returnValue($this->priceMock));
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $renderBlock = $this->getMockBuilder(\Magento\Framework\Pricing\Render\PriceBox::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testedClass = $this->createTestedEntity($data);

        $arguments = [
            'data' => $methodData,
            'rendererPool' => $testedClass,
            'price' => $this->priceMock,
            'saleableItem' => $this->productMock,
        ];
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($className), $this->equalTo(''), $this->equalTo($arguments))
            ->will($this->returnValue($renderBlock));

        $result = $testedClass->createPriceRender($priceCode, $this->productMock, $methodData);
        $this->assertInstanceOf(\Magento\Framework\Pricing\Render\PriceBoxRenderInterface::class, $result);
    }

    /**
     * Create tested object with specified parameters
     *
     * @param array $data
     * @return RendererPool
     */
    protected function createTestedEntity(array $data = [])
    {
        return $this->object = new \Magento\Framework\Pricing\Render\RendererPool($this->contextMock, $data);
    }
}
