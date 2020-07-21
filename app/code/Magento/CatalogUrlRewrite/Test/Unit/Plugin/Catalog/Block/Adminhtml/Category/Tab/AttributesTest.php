<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Plugin\Catalog\Block\Adminhtml\Category\Tab;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;
use Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab\Attributes;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab\Attributes.
 */
class AttributesTest extends TestCase
{
    private const STUB_CATEGORY_META = ['url_key' => 'url_key_test'];
    private const STUB_URL_KEY = 'url_key_777';

    /**
     * @var Attributes
     */
    private $model;

    /**
     * @var Category|MockObject
     */
    private $categoryMock;

    /**
     * @var CategoryDataProvider|MockObject
     */
    private $dataProviderMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->categoryMock = $this->createMock(Category::class);
        $this->dataProviderMock = $this->createMock(CategoryDataProvider::class);
        $this->dataProviderMock->expects($this->any())
            ->method('getCurrentCategory')
            ->willReturn($this->categoryMock);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->model = $objectManager->getObject(Attributes::class, ['scopeConfig' => $this->scopeConfigMock]);
    }

    /**
     * Test get attributes meta
     *
     * @dataProvider attributesMetaDataProvider
     *
     * @param bool $configEnabled
     * @param string $expectedValue
     * @param string $expectedValueMap
     * @return void
     */
    public function testGetAttributesMeta(bool $configEnabled, string $expectedValue, string $expectedValueMap): void
    {
        $this->categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->categoryMock->expects($this->once())
            ->method('getLevel')
            ->willReturn(2);
        $this->categoryMock->expects($this->atMost(2))
            ->method('getUrlKey')
            ->willReturn(self::STUB_URL_KEY);
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->willReturn($configEnabled);
        $this->categoryMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $result = $this->model->afterGetAttributesMeta($this->dataProviderMock, self::STUB_CATEGORY_META);

        $this->assertArrayHasKey('url_key_create_redirect', $result);

        $this->assertArrayHasKey('value', $result['url_key_create_redirect']);
        $this->assertEquals($expectedValue, $result['url_key_create_redirect']['value']);

        $this->assertArrayHasKey('valueMap', $result['url_key_create_redirect']);
        $this->assertArrayHasKey('true', $result['url_key_create_redirect']['valueMap']);
        $this->assertEquals($expectedValueMap, $result['url_key_create_redirect']['valueMap']['true']);

        $this->assertArrayHasKey('disabled', $result['url_key_create_redirect']);
        $this->assertTrue($result['url_key_create_redirect']['disabled']);
    }

    /**
     * DataProvider for testGetAttributesMeta
     *
     * @return array
     */
    public function attributesMetaDataProvider(): array
    {
        return [
            'save rewrite history config enabled' => [true, self::STUB_URL_KEY, self::STUB_URL_KEY],
            'save rewrite history config disabled' => [false, '', 'url_key_777']
        ];
    }

    /**
     * Test get category without id attributes meta
     *
     * @return void
     */
    public function testGetAttributesMetaWithoutCategoryId(): void
    {
        $this->categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $result = $this->model->afterGetAttributesMeta($this->dataProviderMock, self::STUB_CATEGORY_META);

        $this->assertArrayHasKey('url_key_create_redirect', $result);
        $this->assertArrayHasKey('visible', $result['url_key_create_redirect']);
        $this->assertFalse($result['url_key_create_redirect']['visible']);
    }

    /**
     * Test get root category attributes meta
     *
     * @return void
     */
    public function testGetAttributesMetaRootCategory(): void
    {
        $this->categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->categoryMock->expects($this->once())
            ->method('getLevel')
            ->willReturn(1);

        $result = $this->model->afterGetAttributesMeta($this->dataProviderMock, self::STUB_CATEGORY_META);

        $this->assertArrayHasKey('url_key_group', $result);
        $this->assertArrayHasKey('componentDisabled', $result['url_key_group']);
        $this->assertTrue($result['url_key_group']['componentDisabled']);
    }
}
