<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Cms\Model\CustomLayoutManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test pages data provider.
 *
 * @magentoAppArea adminhtml
 */
class DataProviderTest extends TestCase
{
    private $providerData = [
        'name' => 'test',
        'primaryFieldName' => 'page_id',
        'requestFieldName' => 'page_id',
    ];

    /**
     * @var DataProvider
     */
    private $provider;

    /**
     * @var GetPageByIdentifierInterface
     */
    private $repo;

    /**
     * @var CustomLayoutManager
     */
    private $filesFaker;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->configure([
            'preferences' => [CustomLayoutManagerInterface::class => CustomLayoutManager::class]
        ]);
        $this->repo = $this->objectManager->get(GetPageByIdentifierInterface::class);
        $this->filesFaker = $this->objectManager->get(CustomLayoutManager::class);
        $this->request = $this->objectManager->get(HttpRequest::class);
        $this->provider = $this->objectManager->create(
            DataProvider::class,
            array_merge($this->providerData, ['customLayoutManager' => $this->filesFaker])
        );
    }

    /**
     * Check that custom layout date is handled properly.
     *
     * @magentoDataFixture Magento/Cms/_files/pages_with_layout_xml.php
     * @dataProvider customLayoutDataProvider
     *
     * @param string $identifier
     * @param string|null $layoutUpdateSelected
     * @return void
     */
    public function testCustomLayoutData(string $identifier, ?string $layoutUpdateSelected): void
    {
        $page = $this->repo->execute($identifier, 0);

        $request = $this->objectManager->create(RequestInterface::class);
        $request->setParam('page_id', $page->getId());

        $provider = $this->objectManager->create(
            DataProvider::class,
            array_merge($this->providerData, ['request' => $request])
        );

        $data = $provider->getData();
        $pageData = $data[$page->getId()];

        $this->assertEquals($layoutUpdateSelected, $pageData['layout_update_selected']);
    }

    /**
     * DataProvider for testCustomLayoutData
     *
     * @return array
     */
    public static function customLayoutDataProvider(): array
    {
        return [
            ['test_custom_layout_page_1', '_existing_'],
            ['test_custom_layout_page_2', null],
            ['test_custom_layout_page_3', 'test_selected'],
        ];
    }

    /**
     * Check that proper meta for custom layout field is returned.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Cms/_files/pages_with_layout_xml.php
     */
    public function testCustomLayoutMeta(): void
    {
        //Testing a page without layout xml
        $page = $this->repo->execute('test_custom_layout_page_3', 0);
        $this->filesFaker->fakeAvailableFiles((int)$page->getId(), ['test1', 'test2']);
        $this->request->setParam('page_id', $page->getId());

        $meta = $this->provider->getMeta();
        $this->assertArrayHasKey('design', $meta);
        $this->assertArrayHasKey('children', $meta['design']);
        $this->assertArrayHasKey('custom_layout_update_select', $meta['design']['children']);
        $this->assertArrayHasKey('arguments', $meta['design']['children']['custom_layout_update_select']);
        $this->assertArrayHasKey('data', $meta['design']['children']['custom_layout_update_select']['arguments']);
        $this->assertArrayHasKey(
            'options',
            $meta['design']['children']['custom_layout_update_select']['arguments']['data']
        );
        $expectedList = [
            ['label' => 'No update', 'value' => '_no_update_'],
            ['label' => 'test1', 'value' => 'test1'],
            ['label' => 'test2', 'value' => 'test2']
        ];
        $metaList = $meta['design']['children']['custom_layout_update_select']['arguments']['data']['options'];
        sort($expectedList);
        sort($metaList);
        $this->assertEquals($expectedList, $metaList);

        //Page with old layout xml
        $page = $this->repo->execute('test_custom_layout_page_1', 0);
        $this->filesFaker->fakeAvailableFiles((int)$page->getId(), ['test3']);
        $this->request->setParam('page_id', $page->getId());

        $meta = $this->provider->getMeta();
        $this->assertArrayHasKey('design', $meta);
        $this->assertArrayHasKey('children', $meta['design']);
        $this->assertArrayHasKey('custom_layout_update_select', $meta['design']['children']);
        $this->assertArrayHasKey('arguments', $meta['design']['children']['custom_layout_update_select']);
        $this->assertArrayHasKey('data', $meta['design']['children']['custom_layout_update_select']['arguments']);
        $this->assertArrayHasKey(
            'options',
            $meta['design']['children']['custom_layout_update_select']['arguments']['data']
        );
        $expectedList = [
            ['label' => 'No update', 'value' => '_no_update_'],
            ['label' => 'Use existing layout update XML', 'value' => '_existing_'],
            ['label' => 'test3', 'value' => 'test3'],
        ];
        $metaList = $meta['design']['children']['custom_layout_update_select']['arguments']['data']['options'];
        sort($expectedList);
        sort($metaList);
        $this->assertEquals($expectedList, $metaList);
    }
}
