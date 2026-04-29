<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewriteGraphQl\Model\Resolver\AbstractEntityUrl;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for AbstractEntityUrl resolver
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractEntityUrlTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var AbstractEntityUrl|MockObject
     */
    private $resolver;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $urlFinderMock;

    /**
     * @var CustomUrlLocatorInterface|MockObject
     */
    private $customUrlLocatorMock;

    /**
     * @var Uid|MockObject
     */
    private $idEncoderMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $extensionAttributesMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var UrlRewrite|MockObject
     */
    private $urlRewriteMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlFinderMock = $this->createMock(UrlFinderInterface::class);
        $this->customUrlLocatorMock = $this->createMock(CustomUrlLocatorInterface::class);
        $this->idEncoderMock = $this->createMock(Uid::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->urlRewriteMock = $this->createMock(UrlRewrite::class);

        $this->extensionAttributesMock = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['getStore']
        );
        $this->extensionAttributesMock->method('getStore')
            ->willReturn($this->storeMock);

        $this->contextMock->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->resolver = $this->getMockBuilder(AbstractEntityUrl::class)
            ->setConstructorArgs([
                $this->urlFinderMock,
                $this->customUrlLocatorMock,
                $this->idEncoderMock
            ])
            ->onlyMethods([])
            ->getMock();
    }

    /**
     * Test resolve method throws exception when url argument is missing
     */
    public function testResolveThrowsExceptionWhenUrlArgumentMissing(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"url" argument should be specified and not empty');

        $this->resolver->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, null, []);
    }

    /**
     * Test resolve method throws exception when url argument is empty
     */
    public function testResolveThrowsExceptionWhenUrlArgumentEmpty(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"url" argument should be specified and not empty');

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => '']
        );
    }

    /**
     * Test resolve method throws exception when url argument is whitespace only
     */
    public function testResolveThrowsExceptionWhenUrlArgumentWhitespaceOnly(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"url" argument should be specified and not empty');

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => '   ']
        );
    }

    /**
     * Test resolve method returns null when no URL rewrite found
     */
    public function testResolveReturnsNullWhenNoUrlRewriteFound(): void
    {
        $url = 'test-url';
        $storeId = 1;

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with($url)->willReturn(null);
        $this->urlFinderMock->method('findOneByData')->willReturn(null);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertNull($result);
    }

    /**
     * Test resolve method throws exception when entity not found for non-redirect URL
     */
    public function testResolveThrowsExceptionWhenEntityNotFoundForNonRedirectUrl(): void
    {
        $url = 'test-url';
        $storeId = 1;

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with($url)->willReturn(null);

        // Create a second URL rewrite mock for the entity URL rewrite that will be found
        $entityUrlRewriteMock = $this->createMock(UrlRewrite::class);
        $entityUrlRewriteMock->method('getEntityId')->willReturn(null);
        $entityUrlRewriteMock->method('getEntityType')->willReturn('product');

        $this->urlRewriteMock->method('getRedirectType')->willReturn(0);
        $this->urlRewriteMock->method('getEntityId')->willReturn(null);
        $this->urlRewriteMock->method('getEntityType')->willReturn('product');
        $this->urlRewriteMock->method('getTargetPath')->willReturn('catalog/product/view/id/123');
        $this->urlRewriteMock->method('getStoreId')->willReturn($storeId);

        $urlRewriteMock = $this->urlRewriteMock;
        $this->urlFinderMock->expects($this->exactly(3))
            ->method('findOneByData')
            ->willReturnCallback(function ($data) use ($urlRewriteMock, $entityUrlRewriteMock) {
                if (isset($data['request_path']) && $data['request_path'] === 'test-url') {
                    return $urlRewriteMock;
                }
                if (isset($data['request_path']) && $data['request_path'] === 'catalog/product/view/id/123') {
                    return null; // No redirect for final URL
                }
                if (isset($data['target_path'])) {
                    return $entityUrlRewriteMock; // Return entity URL rewrite but with no entity ID
                }
                return null;
            });

        $this->expectException(GraphQlNoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity found with matching URL key: test-url');

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );
    }

    /**
     * Test resolve method returns data for valid URL with entity
     */
    public function testResolveReturnsDataForValidUrlWithEntity(): void
    {
        $url = 'test-product';
        $storeId = 1;
        $entityId = 123;
        $entityType = 'product';
        $encodedId = 'encoded123';

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with($url)->willReturn(null);

        $this->urlRewriteMock->method('getRedirectType')->willReturn(0);
        $this->urlRewriteMock->method('getEntityId')->willReturn($entityId);
        $this->urlRewriteMock->method('getEntityType')->willReturn($entityType);
        $this->urlRewriteMock->method('getRequestPath')->willReturn($url);
        $this->urlRewriteMock->method('getTargetPath')->willReturn('catalog/product/view/id/123');
        $this->urlRewriteMock->method('getStoreId')->willReturn($storeId);

        $this->urlFinderMock->method('findOneByData')
            ->willReturn($this->urlRewriteMock);

        $this->idEncoderMock->method('encode')
            ->with((string)$entityId)
            ->willReturn($encodedId);

        $expectedResult = [
            'id' => $entityId,
            'entity_uid' => $encodedId,
            'canonical_url' => $url,
            'relative_url' => $url,
            'redirectCode' => 0,
            'redirect_code' => 0,
            'type' => 'PRODUCT'
        ];

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test resolve method handles redirect URL correctly
     */
    public function testResolveHandlesRedirectUrlCorrectly(): void
    {
        $url = 'old-product-url';
        $targetPath = 'new-product-url';
        $storeId = 1;
        $entityId = 123;
        $entityType = 'product';
        $redirectType = 301;
        $encodedId = 'encoded123';

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with($url)->willReturn(null);

        $this->urlRewriteMock->method('getRedirectType')->willReturn($redirectType);
        $this->urlRewriteMock->method('getEntityId')->willReturn($entityId);
        $this->urlRewriteMock->method('getEntityType')->willReturn($entityType);
        $this->urlRewriteMock->method('getRequestPath')->willReturn($url);
        $this->urlRewriteMock->method('getTargetPath')->willReturn($targetPath);
        $this->urlRewriteMock->method('getStoreId')->willReturn($storeId);

        $this->urlFinderMock->method('findOneByData')
            ->willReturn($this->urlRewriteMock);

        $this->idEncoderMock->method('encode')
            ->with((string)$entityId)
            ->willReturn($encodedId);

        $expectedResult = [
            'id' => $entityId,
            'entity_uid' => $encodedId,
            'canonical_url' => $targetPath,
            'relative_url' => $targetPath,
            'redirectCode' => $redirectType,
            'redirect_code' => $redirectType,
            'type' => 'PRODUCT'
        ];

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test resolve method handles URL with query parameters
     */
    public function testResolveHandlesUrlWithQueryParameters(): void
    {
        $url = 'test-product?param1=value1&param2=value2';
        $storeId = 1;
        $entityId = 123;
        $entityType = 'product';
        $encodedId = 'encoded123';

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with('test-product')->willReturn(null);

        $this->urlRewriteMock->method('getRedirectType')->willReturn(0);
        $this->urlRewriteMock->method('getEntityId')->willReturn($entityId);
        $this->urlRewriteMock->method('getEntityType')->willReturn($entityType);
        $this->urlRewriteMock->method('getRequestPath')->willReturn('test-product');
        $this->urlRewriteMock->method('getTargetPath')->willReturn('catalog/product/view/id/123');
        $this->urlRewriteMock->method('getStoreId')->willReturn($storeId);

        $this->urlFinderMock->method('findOneByData')
            ->willReturn($this->urlRewriteMock);

        $this->idEncoderMock->method('encode')
            ->with((string)$entityId)
            ->willReturn($encodedId);

        $expectedResult = [
            'id' => $entityId,
            'entity_uid' => $encodedId,
            'canonical_url' => 'test-product?param1=value1&param2=value2',
            'relative_url' => 'test-product?param1=value1&param2=value2',
            'redirectCode' => 0,
            'redirect_code' => 0,
            'type' => 'PRODUCT'
        ];

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test resolve method handles custom URL locator result
     */
    public function testResolveHandlesCustomUrlLocatorResult(): void
    {
        $url = 'custom-url';
        $customUrl = 'modified-custom-url';
        $storeId = 1;
        $entityId = 123;
        $entityType = 'product';
        $encodedId = 'encoded123';

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with($url)->willReturn($customUrl);

        $this->urlRewriteMock->method('getRedirectType')->willReturn(0);
        $this->urlRewriteMock->method('getEntityId')->willReturn($entityId);
        $this->urlRewriteMock->method('getEntityType')->willReturn($entityType);
        $this->urlRewriteMock->method('getRequestPath')->willReturn($customUrl);
        $this->urlRewriteMock->method('getTargetPath')->willReturn('catalog/product/view/id/123');
        $this->urlRewriteMock->method('getStoreId')->willReturn($storeId);

        $this->urlFinderMock->method('findOneByData')
            ->willReturn($this->urlRewriteMock);

        $this->idEncoderMock->method('encode')
            ->with((string)$entityId)
            ->willReturn($encodedId);

        $expectedResult = [
            'id' => $entityId,
            'entity_uid' => $encodedId,
            'canonical_url' => $customUrl,
            'relative_url' => $customUrl,
            'redirectCode' => 0,
            'redirect_code' => 0,
            'type' => 'PRODUCT'
        ];

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test resolve method handles URL chain redirects
     */
    public function testResolveHandlesUrlChainRedirects(): void
    {
        $url = 'old-url';
        $storeId = 1;
        $entityId = 123;
        $entityType = 'product';
        $encodedId = 'encoded123';

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with($url)->willReturn(null);

        // First URL rewrite - redirects to intermediate URL
        $firstUrlRewrite = $this->createMock(UrlRewrite::class);
        $firstUrlRewrite->method('getRedirectType')->willReturn(0);
        $firstUrlRewrite->method('getEntityId')->willReturn($entityId);
        $firstUrlRewrite->method('getEntityType')->willReturn($entityType);
        $firstUrlRewrite->method('getRequestPath')->willReturn($url);
        $firstUrlRewrite->method('getTargetPath')->willReturn('intermediate-url');
        $firstUrlRewrite->method('getStoreId')->willReturn($storeId);

        // Second URL rewrite - final target
        $secondUrlRewrite = $this->createMock(UrlRewrite::class);
        $secondUrlRewrite->method('getRedirectType')->willReturn(0);
        $secondUrlRewrite->method('getEntityId')->willReturn($entityId);
        $secondUrlRewrite->method('getEntityType')->willReturn($entityType);
        $secondUrlRewrite->method('getRequestPath')->willReturn('intermediate-url');
        $secondUrlRewrite->method('getTargetPath')->willReturn('final-url');
        $secondUrlRewrite->method('getStoreId')->willReturn($storeId);

        // Third call should return null to break the chain
        $thirdUrlRewrite = $this->createMock(UrlRewrite::class);
        $thirdUrlRewrite->method('getRedirectType')->willReturn(0);
        $thirdUrlRewrite->method('getEntityId')->willReturn($entityId);
        $thirdUrlRewrite->method('getEntityType')->willReturn($entityType);
        $thirdUrlRewrite->method('getRequestPath')->willReturn('final-url');
        $thirdUrlRewrite->method('getTargetPath')->willReturn('catalog/product/view/id/123');
        $thirdUrlRewrite->method('getStoreId')->willReturn($storeId);

        $this->urlFinderMock->expects($this->exactly(4))
            ->method('findOneByData')
            ->willReturnCallback(function ($data) use ($firstUrlRewrite, $secondUrlRewrite, $thirdUrlRewrite) {
                if (isset($data['request_path'])) {
                    switch ($data['request_path']) {
                        case 'old-url':
                            return $firstUrlRewrite;
                        case 'intermediate-url':
                            return $secondUrlRewrite;
                        case 'final-url':
                            return $thirdUrlRewrite;
                        case 'catalog/product/view/id/123':
                            return null;
                    }
                }
                return null;
            });

        $this->idEncoderMock->method('encode')
            ->with((string)$entityId)
            ->willReturn($encodedId);

        $expectedResult = [
            'id' => $entityId,
            'entity_uid' => $encodedId,
            'canonical_url' => $url,
            'relative_url' => $url,
            'redirectCode' => 0,
            'redirect_code' => 0,
            'type' => 'PRODUCT'
        ];

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test resolve method handles URL with leading slash
     */
    public function testResolveHandlesUrlWithLeadingSlash(): void
    {
        $url = '/test-product';
        $storeId = 1;
        $entityId = 123;
        $entityType = 'product';
        $encodedId = 'encoded123';

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with('test-product')->willReturn(null);

        $this->urlRewriteMock->method('getRedirectType')->willReturn(0);
        $this->urlRewriteMock->method('getEntityId')->willReturn($entityId);
        $this->urlRewriteMock->method('getEntityType')->willReturn($entityType);
        $this->urlRewriteMock->method('getRequestPath')->willReturn('test-product');
        $this->urlRewriteMock->method('getTargetPath')->willReturn('catalog/product/view/id/123');
        $this->urlRewriteMock->method('getStoreId')->willReturn($storeId);

        $this->urlFinderMock->method('findOneByData')
            ->willReturn($this->urlRewriteMock);

        $this->idEncoderMock->method('encode')
            ->with((string)$entityId)
            ->willReturn($encodedId);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertNotNull($result);
        $this->assertEquals($entityId, $result['id']);
    }

    /**
     * Test resolve method handles root URL "/"
     */
    public function testResolveHandlesRootUrl(): void
    {
        $url = '/';
        $storeId = 1;

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with('/')->willReturn(null);

        $this->urlFinderMock->method('findOneByData')->willReturn(null);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertNull($result);
    }

    /**
     * Test sanitizeType method with various entity types
     */
    public function testSanitizeTypeHandlesVariousEntityTypes(): void
    {
        $testCases = [
            'product' => 'PRODUCT',
            'category' => 'CATEGORY',
            'cms-page' => 'CMS_PAGE',
            'custom-entity' => 'CUSTOM_ENTITY',
            'multi-word-entity' => 'MULTI_WORD_ENTITY'
        ];

        $reflection = new \ReflectionClass($this->resolver);
        $method = $reflection->getMethod('sanitizeType');

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->resolver, $input);
            $this->assertEquals($expected, $result, "Failed for input: $input");
        }
    }

    /**
     * Test parseUrl method with various URL formats
     */
    public function testParseUrlHandlesVariousUrlFormats(): void
    {
        $testCases = [
            // Simple URL without leading slash
            'simple-url' => ['path' => 'simple-url'],
            // URL with leading slash (should be stripped)
            '/leading-slash' => ['path' => 'leading-slash'],
            // URL with query parameters
            'url-with-query?param=value' => ['path' => 'url-with-query', 'query' => 'param=value'],
            // Complex path structure
            'complex/path/structure' => ['path' => 'complex/path/structure'],
            // Root URL (leading slash should be preserved)
            '/' => ['path' => '/'],
            // Empty string
            '' => ['path' => ''],

            // URL with fragment
            'path-with-fragment#section' => ['path' => 'path-with-fragment', 'fragment' => 'section'],
            // URL with query and fragment
            '/path?query=value#fragment' => ['path' => 'path', 'query' => 'query=value', 'fragment' => 'fragment'],
            // URL with host (should preserve path parsing)
            'https://example.com/path' => ['scheme' => 'https', 'host' => 'example.com', 'path' => 'path'],
            // URL with port
            'http://example.com:8080/path' => [
                'scheme' => 'http',
                'host' => 'example.com',
                'port' => 8080,
                'path' => 'path'
            ],
            // URL with user info
            'https://user:pass@example.com/path' => [
                'scheme' => 'https',
                'user' => 'user',
                'pass' => 'pass',
                'host' => 'example.com',
                'path' => 'path'
            ],
            // Complex query string
            'product?color=red&size=large&in_stock=1' => [
                'path' => 'product',
                'query' => 'color=red&size=large&in_stock=1'
            ],
            // Path with encoded characters
            'category/special%20products' => ['path' => 'category/special%20products'],
            // Multiple slashes in path
            '///multiple///slashes' => ['path' => 'multiple///slashes']
        ];

        $reflection = new \ReflectionClass($this->resolver);
        $method = $reflection->getMethod('parseUrl');

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->resolver, $input);
            $this->assertEquals($expected, $result, "Failed for input: $input");
        }
    }

    /**
     * Test parseUrl method with malformed URLs
     */
    public function testParseUrlHandlesMalformedUrls(): void
    {
        $reflection = new \ReflectionClass($this->resolver);
        $method = $reflection->getMethod('parseUrl');

        // Test cases where parse_url might return false or fail
        $malformedUrls = [
            // Very malformed URL that might cause parse_url to return false
            "http:///",
            // URL with invalid characters
            "test\x00url",
            // Extremely long URL (though this might not fail parse_url)
            str_repeat('a', 2000),
        ];

        foreach ($malformedUrls as $malformedUrl) {
            $result = $method->invoke($this->resolver, $malformedUrl);
            // Should always return an array with at least 'path' key
            $this->assertIsArray($result);
            $this->assertArrayHasKey('path', $result);
            // When parse_url fails, the path should be the original URL
            if (!is_array(parse_url($malformedUrl))) {
                $this->assertEquals($malformedUrl, $result['path']);
            }
        }
    }

    /**
     * Test parseUrl method with edge cases for path normalization
     */
    public function testParseUrlPathNormalization(): void
    {
        $reflection = new \ReflectionClass($this->resolver);
        $method = $reflection->getMethod('parseUrl');

        // Test edge cases for path normalization
        $testCases = [
            // Single slash should remain as is (root)
            '/' => ['path' => '/'],
            // Multiple leading slashes should be stripped to single path
            '/////path' => ['path' => 'path'],
            // Leading slash with query should strip slash from path
            '/path?query=test' => ['path' => 'path', 'query' => 'query=test'],
            // Leading slash with fragment should strip slash from path
            '/path#fragment' => ['path' => 'path', 'fragment' => 'fragment'],
            // Only slashes (not root) should be stripped
            '//' => ['path' => ''],
            '///' => ['path' => ''],
        ];

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->resolver, $input);
            $this->assertEquals($expected, $result, "Failed for input: '$input'");
        }
    }

    /**
     * Test resolve method when finding URL from target path after request path fails
     */
    public function testResolveFindsUrlFromTargetPath(): void
    {
        $url = 'catalog/product/view/id/123';
        $storeId = 1;
        $entityId = 123;
        $entityType = 'product';
        $encodedId = 'encoded123';

        $this->storeMock->method('getId')->willReturn($storeId);
        $this->customUrlLocatorMock->method('locateUrl')->with($url)->willReturn(null);

        $this->urlRewriteMock->method('getRedirectType')->willReturn(0);
        $this->urlRewriteMock->method('getEntityId')->willReturn($entityId);
        $this->urlRewriteMock->method('getEntityType')->willReturn($entityType);
        $this->urlRewriteMock->method('getRequestPath')->willReturn('product-url');
        $this->urlRewriteMock->method('getTargetPath')->willReturn($url);
        $this->urlRewriteMock->method('getStoreId')->willReturn($storeId);

        $urlRewriteMock = $this->urlRewriteMock;
        $this->urlFinderMock->expects($this->exactly(3))
            ->method('findOneByData')
            ->willReturnCallback(function ($data) use ($urlRewriteMock) {
                if (isset($data['request_path']) && $data['request_path'] === 'catalog/product/view/id/123') {
                    return null; // No URL found by request path
                }
                if (isset($data['request_path']) && $data['request_path'] === $urlRewriteMock->getTargetPath()) {
                    return null; // No redirect chain
                }
                if (isset($data['target_path'])) {
                    return $urlRewriteMock; // Found by target path
                }
                return null;
            });

        $this->idEncoderMock->method('encode')
            ->with((string)$entityId)
            ->willReturn($encodedId);

        $expectedResult = [
            'id' => $entityId,
            'entity_uid' => $encodedId,
            'canonical_url' => 'product-url',
            'relative_url' => 'product-url',
            'redirectCode' => 0,
            'redirect_code' => 0,
            'type' => 'PRODUCT'
        ];

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['url' => $url]
        );

        $this->assertEquals($expectedResult, $result);
    }
}
