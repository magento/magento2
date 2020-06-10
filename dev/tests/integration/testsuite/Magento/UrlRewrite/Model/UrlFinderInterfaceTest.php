<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrites.php
 */
class UrlFinderInterfaceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlFinder = Bootstrap::getObjectManager()->create(UrlFinderInterface::class);
    }

    /**
     * @dataProvider findOneDataProvider
     * @param string $requestPath
     * @param string $targetPath
     * @param int $redirectType
     */
    public function testFindOneByData(string $requestPath, string $targetPath, int $redirectType)
    {
        $data = [
            UrlRewrite::REQUEST_PATH => $requestPath,
        ];
        $urlRewrite = $this->urlFinder->findOneByData($data);
        $this->assertEquals($targetPath, $urlRewrite->getTargetPath());
        $this->assertEquals($redirectType, $urlRewrite->getRedirectType());
    }

    /**
     * @return array
     */
    public function findOneDataProvider(): array
    {
        return [
            ['string', 'test_page1', 0],
            ['string/', 'string', 301],
            ['string_permanent', 'test_page1', 301],
            ['string_permanent/', 'test_page1', 301],
            ['string_temporary', 'test_page1', 302],
            ['string_temporary/', 'test_page1', 302],
            ['строка', 'test_page1', 0],
            ['строка/', 'строка', 301],
            [urlencode('строка'), 'test_page2', 0],
            [urlencode('строка') . '/', urlencode('строка'), 301],
            ['другая_строка', 'test_page1', 302],
            ['другая_строка/', 'test_page1', 302],
            [urlencode('другая_строка'), 'test_page1', 302],
            [urlencode('другая_строка') . '/', 'test_page1', 302],
            ['السلسلة', 'test_page1', 0],
            [urlencode('السلسلة'), 'test_page1', 0],
        ];
    }
}
