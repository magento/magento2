<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model\StoreSwitcher;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\PhpEnvironment\RequestFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\UrlRewrite\Model\StoreSwitcher\RewriteUrl;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @see RewriteUrl
 */
class RewriteUrlTest extends TestCase
{
    /**
     * Website 1 has store 1, website 2 has stores 7, 8 and 25.
     */
    private const WEBSITE_STORES = [1 => [1 => '1'], 2 => [7 => '7', 8 => '8', 25 => '25']];

    /**
     * @param array $rewrites
     * @param string $expectedUrl
     * @return void
     */
    #[DataProvider('switchDataProvider')]
    public function testSwitchFromPathWithoutRewriteInFromStore(array $rewrites, string $expectedUrl): void
    {
        $urlFinder = $this->createStub(UrlFinderInterface::class);
        $urlFinder->method('findOneByData')->willReturnCallback(
            function (array $data) use ($rewrites) {
                foreach ($rewrites as $rewrite) {
                    $matches = true;
                    foreach ($data as $field => $value) {
                        $matches = $matches && in_array($rewrite[$field], (array)$value);
                    }
                    if ($matches) {
                        return $this->createStub(UrlRewrite::class);
                    }
                }
                return null;
            }
        );

        $requestFactory = $this->createStub(RequestFactory::class);
        $requestFactory->method('create')->willReturn(new DataObject(['path_info' => '/blog']));

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getWebsite')->willReturnCallback(
            function ($websiteId) {
                $website = $this->createStub(Website::class);
                $website->method('getStoreIds')->willReturn(self::WEBSITE_STORES[$websiteId]);
                return $website;
            }
        );

        $model = new RewriteUrl($urlFinder, $requestFactory, $storeManager);
        $this->assertSame(
            $expectedUrl,
            $model->switch($this->getStore(7), $this->getStore(25), 'https://b.example/blog')
        );
    }

    /**
     * @return array
     */
    public static function switchDataProvider(): array
    {
        return [
            'rewrite only in another website, the url is kept' => [
                [[UrlRewrite::REQUEST_PATH => 'blog', UrlRewrite::STORE_ID => 1]],
                'https://b.example/blog',
            ],
            'rewrite in another store of the same website, redirect to the home page' => [
                [[UrlRewrite::REQUEST_PATH => 'blog', UrlRewrite::STORE_ID => 8]],
                'https://b.example/en/',
            ],
        ];
    }

    /**
     * Create a store of website 2
     *
     * @param int $storeId
     * @return Store
     */
    private function getStore(int $storeId): Store
    {
        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn($storeId);
        $store->method('getWebsiteId')->willReturn(2);
        $store->method('isUseStoreInUrl')->willReturn(false);
        $store->method('getBaseUrl')->willReturn('https://b.example/en/');
        return $store;
    }
}
