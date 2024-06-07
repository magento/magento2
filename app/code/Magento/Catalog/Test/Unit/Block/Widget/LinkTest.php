<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Widget;

use Exception;
use Magento\Catalog\Block\Widget\Link;
use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\Url\ModifierInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkTest extends TestCase
{
    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MockObject|UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var Link
     */
    protected $block;

    /**
     * @var AbstractResource|MockObject
     */
    protected $entityResource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->urlFinder = $this->getMockForAbstractClass(UrlFinderInterface::class);

        $context = $this->createMock(Context::class);
        $context->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->storeManager);

        $this->entityResource =
            $this->createMock(AbstractResource::class);

        $this->block = $objectManager->getObject(
            Link::class,
            [
                'context' => $context,
                'urlFinder' => $this->urlFinder,
                'entityResource' => $this->entityResource
            ]
        );
    }

    /**
     * Tests getHref with wrong id_path
     */
    public function testGetHrefWithoutSetIdPath()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Parameter id_path is not set.');
        $this->block->getHref();
    }

    /**
     * Tests getHref with wrong id_path
     */
    public function testGetHrefIfSetWrongIdPath()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Wrong id_path structure.');
        $this->block->setData('id_path', 'wrong_id_path');
        $this->block->getHref();
    }

    /**
     * Tests getHref with wrong store ID
     */
    public function testGetHrefWithSetStoreId()
    {
        $this->expectException('Exception');
        $this->block->setData('id_path', 'type/id');
        $this->block->setData('store_id', 'store_id');
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with('store_id')
            ->willThrowException(new Exception());
        $this->block->getHref();
    }

    /**
     * Tests getHref with not found URL
     */
    public function testGetHrefIfRewriteIsNotFound()
    {
        $this->block->setData('id_path', 'entity_type/entity_id');

        $store = $this->createPartialMock(Store::class, ['getId']);
        $store->expects($this->any())
            ->method('getId');

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->urlFinder->expects($this->once())->method('findOneByData')
            ->willReturn(false);

        $this->assertFalse($this->block->getHref());
    }

    /**
     * Tests getHref whether it should include the store code or not
     *
     * @dataProvider dataProviderForTestGetHrefWithoutUrlStoreSuffix
     * @param string $path
     * @param int|null $storeId
     * @param bool $includeStoreCode
     * @param string $expected
     * @throws \ReflectionException
     */
    public function testStoreCodeShouldBeIncludedInURLOnlyIfItIsConfiguredSo(
        string $path,
        ?int $storeId,
        bool $includeStoreCode,
        string $expected
    ) {
        $this->block->setData('id_path', 'entity_type/entity_id');
        $this->block->setData('store_id', $storeId);
        $objectManager = new ObjectManager($this);

        $rewrite = $this->createPartialMock(UrlRewrite::class, ['getRequestPath']);
        $url = $this->createPartialMock(Url::class, ['setScope', 'getUrl']);
        $urlModifier = $this->getMockForAbstractClass(ModifierInterface::class);
        $config = $this->getMockForAbstractClass(ReinitableConfigInterface::class);
        $store = $objectManager->getObject(
            Store::class,
            [
                'storeManager' => $this->storeManager,
                'url' => $url,
                'config' => $config
            ]
        );
        $property = (new ReflectionClass(get_class($store)))->getProperty('urlModifier');
        $property->setAccessible(true);
        $property->setValue($store, $urlModifier);

        $urlModifier->expects($this->any())
            ->method('execute')
            ->willReturnArgument(0);
        $config->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [Store::XML_PATH_USE_REWRITES, ReinitableConfigInterface::SCOPE_TYPE_DEFAULT, null, true],
                    [Store::XML_PATH_UNSECURE_BASE_LINK_URL, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, ''],
                    [
                        Store::XML_PATH_STORE_IN_URL,
                        ReinitableConfigInterface::SCOPE_TYPE_DEFAULT,
                        null, $includeStoreCode
                    ]
                ]
            );

        $url->expects($this->any())
            ->method('setScope')
            ->willReturnSelf();

        $url->expects($this->any())
            ->method('getUrl')
            ->willReturnCallback(
                function ($route, $params) use ($storeId) {
                    $baseUrl = rtrim($this->storeManager->getStore($storeId)->getBaseUrl(), '/');
                    return $baseUrl . '/' . ltrim($params['_direct'], '/');
                }
            );

        $store->addData(['store_id' => 1, 'code' => 'french']);

        $store2 = clone $store;
        $store2->addData(['store_id' => 2, 'code' => 'german']);

        $this->storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturnMap(
                [
                    [null, $store],
                    [1, $store],
                    [2, $store2],
                ]
            );

        $this->urlFinder->expects($this->once())
            ->method('findOneByData')
            ->with(
                [
                    UrlRewrite::ENTITY_ID => 'entity_id',
                    UrlRewrite::ENTITY_TYPE => 'entity_type',
                    UrlRewrite::STORE_ID => $this->storeManager->getStore($storeId)->getStoreId(),
                ]
            )
            ->willReturn($rewrite);

        $rewrite->expects($this->once())
            ->method('getRequestPath')
            ->willReturn($path);

        $this->assertEquals($expected, $this->block->getHref());
    }

    /**
     * Tests getLabel with custom text
     */
    public function testGetLabelWithCustomText()
    {
        $customText = 'Some text';
        $this->block->setData('anchor_text', $customText);
        $this->assertEquals($customText, $this->block->getLabel());
    }

    /**
     * Tests getLabel without custom text
     */
    public function testGetLabelWithoutCustomText()
    {
        $category = 'Some text';
        $id = 1;
        $idPath = 'id/' . $id;
        $store = 1;

        $this->block->setData('id_path', $idPath);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $this->entityResource->expects($this->once())->method('getAttributeRawValue')->with($id, 'name', $store)
            ->willReturn($category);
        $this->assertEquals($category, $this->block->getLabel());
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetHrefWithoutUrlStoreSuffix()
    {
        return [
            ['/accessories.html', null, true, 'french/accessories.html'],
            ['/accessories.html', null, false, '/accessories.html'],
            ['/accessories.html', 1, true, 'french/accessories.html'],
            ['/accessories.html', 1, false, '/accessories.html'],
            ['/accessories.html', 2, true, 'german/accessories.html'],
            ['/accessories.html', 2, false, '/accessories.html?___store=german'],
            ['/accessories.html?___store=german', 2, false, '/accessories.html?___store=german'],
        ];
    }

    /**
     * Tests getHref with product entity and additional category id in the id_path
     */
    public function testGetHrefWithForProductWithCategoryIdParameter()
    {
        $storeId = 15;
        $this->block->setData('id_path', ProductUrlRewriteGenerator::ENTITY_TYPE . '/entity_id/category_id');

        $store = $this->createPartialMock(Store::class, ['getId']);
        $store->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->urlFinder->expects($this->once())
            ->method('findOneByData')
            ->with(
                [
                    UrlRewrite::ENTITY_ID => 'entity_id',
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storeId,
                    UrlRewrite::METADATA => ['category_id' => 'category_id'],
                ]
            )
            ->willReturn(false);

        $this->block->getHref();
    }
}
