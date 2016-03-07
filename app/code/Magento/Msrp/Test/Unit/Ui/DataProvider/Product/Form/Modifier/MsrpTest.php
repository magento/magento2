<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Msrp\Ui\DataProvider\Product\Form\Modifier\Msrp;
use Magento\Catalog\Ui\DataProvider\Grouper;
use Magento\Msrp\Model\Config as MsrpConfig;

/**
 * Class MsrpTest
 */
class MsrpTest extends AbstractModifierTest
{
    /**
     * @var Grouper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $grouperMock;

    /**
     * @var MsrpConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $msrpConfigMock;

    protected function setUp()
    {
        $this->grouperMock = $this->getMockBuilder(Grouper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->msrpConfigMock = $this->getMockBuilder(MsrpConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Msrp::class, [
            'locator' => $this->locatorMock,
            'grouper' => $this->grouperMock,
            'msrpConfig' => $this->msrpConfigMock,
        ]);
    }

    public function testModifyData()
    {
        $this->assertSame([], $this->getModel()->modifyData([]));

        $productId = 1;

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->assertArrayHasKey($productId, $this->getModel()->modifyData([
            $productId => [
                Msrp::DATA_SOURCE_DEFAULT => [
                    Msrp::FIELD_MSRP => 2
                ],
            ],
        ]));
    }

    public function testModifyMeta()
    {
        $this->assertSame([], $this->getModel()->modifyMeta([]));
    }
}
