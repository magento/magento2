<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Product\CanonicalUrl;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class CanonicalUrlTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CanonicalUrl
     */
    private $subject;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockValueFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockStoreManager;

    public function testReturnsNullWhenNoProductAvailable()
    {
        $mockField = $this->getMockBuilder(\Magento\Framework\GraphQl\Config\Element\Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockInfo = $this->getMockBuilder(\Magento\Framework\GraphQl\Schema\Type\ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockValueFactory->method('create')->with(
            $this->callback(
                function ($param) {
                    return $param() === null;
                }
            )
        );

        $this->subject->resolve($mockField, '', $mockInfo, [], []);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = new ObjectManager($this);
        $this->mockStoreManager = $this->getMockBuilder(StoreManagerInterface::class)->getMock();
        $this->mockValueFactory = $this->getMockBuilder(ValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockValueFactory->method('create')->willReturn(
            $this->objectManager->getObject(
                Value::class,
                ['callback' => function () {
                    return '';
                }]
            )
        );

        $mockProductUrlPathGenerator = $this->getMockBuilder(ProductUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockProductUrlPathGenerator->method('getUrlPathWithSuffix')->willReturn('product_url.html');

        $this->subject = $this->objectManager->getObject(
            CanonicalUrl::class,
            [
                'valueFactory' => $this->mockValueFactory,
                'storeManager' => $this->mockStoreManager,
                'productUrlPathGenerator' => $mockProductUrlPathGenerator
            ]
        );
    }
}
