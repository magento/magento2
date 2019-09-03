<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Product\Attribute\Backend\LayoutUpdate as LayoutUpdateAttribute;

/**
 * Test the modifier.
 */
class LayoutUpdateTest extends TestCase
{
    /**
     * @var LayoutUpdate
     */
    private $modifier;

    /**
     * @var ProductRepositoryInterface
     */
    private $repo;

    /**
     * @var MockObject
     */
    private $locator;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->locator = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->modifier = Bootstrap::getObjectManager()->create(LayoutUpdate::class, ['locator' => $this->locator]);
        $this->repo = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * Test that data is being modified accordingly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->repo->get('simple');
        $this->locator->method('getProduct')->willReturn($product);
        $product->setCustomAttribute('custom_layout_update', 'something');

        $data = $this->modifier->modifyData([$product->getId() => ['product' => []]]);
        $this->assertEquals(
            LayoutUpdateAttribute::VALUE_USE_UPDATE_XML,
            $data[$product->getId()]['product']['custom_layout_update_file']
        );
    }
}
