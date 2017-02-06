<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel as ConfigurablePanelModifier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class ConfigurablePanelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurablePanelModifier
     */
    private $configurablePanelModifier;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLocatorMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    protected function setUp()
    {
        $this->productLocatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();

        $this->productLocatorMock->expects(static::any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->urlBuilderMock->expects(static::any())
            ->method('addSessionParam')
            ->willReturnSelf();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configurablePanelModifier = $this->objectManagerHelper->getObject(
            ConfigurablePanelModifier::class,
            [
                'locator' => $this->productLocatorMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    public function testModifyMeta()
    {
        $result = $this->configurablePanelModifier->modifyMeta([]);

        $this->assertArrayHasKey(ConfigurablePanelModifier::GROUP_CONFIGURABLE, $result);
        $this->assertArrayHasKey(ConfigurablePanelModifier::ASSOCIATED_PRODUCT_MODAL, $result);
    }
}
