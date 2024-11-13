<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class for test Category Data Provider
 *
 * @see \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
 *
 * @magentoAppArea adminhtml
 */
class CategoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Category
     */
    private $provider;

    /**
     * @var CategoryInterfaceFactory
     */
    private $categoryFactory;

    /**
     * @var Registry
     */
    private $registry;

    /** @var Resolver */
    private $layerResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFactory = $this->objectManager->get(CategoryInterfaceFactory::class);
        $this->layerResolver = $this->objectManager->get(Resolver::class);
        $this->provider = $this->objectManager->create(Category::class, ['layer' => $this->layerResolver->get()]);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @return void
     */
    public function testValidateCategoryWithoutId(): void
    {
        $this->registry->register('current_category', $this->categoryFactory->create());
        $this->provider->setCategoryId(375211);
        $this->assertFalse($this->provider->isValid());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/inactive_category.php
     *
     * @return void
     */
    public function testValidateInactiveCategory(): void
    {
        $this->provider->setCategoryId(111);
        $this->assertFalse($this->provider->isValid());
    }
}
