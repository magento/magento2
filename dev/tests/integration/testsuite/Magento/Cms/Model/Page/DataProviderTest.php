<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Cms\Model\Page as PageModel;
use Magento\Cms\Model\PageFactory as PageModelFactory;

/**
 * Test pages data provider.
 */
class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $provider;

    /**
     * @var PageModelFactory
     */
    private $pageFactory;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->pageFactory = $objectManager->get(PageModelFactory::class);
        $this->provider = $objectManager->create(
            DataProvider::class,
            [
                'name' => 'test',
                'primaryFieldName' => 'page_id',
                'requestFieldName' => 'page_id'
            ]
        );
    }

    /**
     * Check that custom layout date is handled properly.
     *
     * @magentoDataFixture Magento/Cms/_files/pages_with_layout_xml.php
     * @throws \Throwable
     * @return void
     */
    public function testCustomLayoutData(): void
    {
        $data = $this->provider->getData();
        $page1Data = null;
        $page2Data = null;
        foreach ($data as $pageData) {
            if ($pageData[PageModel::IDENTIFIER] === 'test_custom_layout_page_1') {
                $page1Data = $pageData;
            } elseif ($pageData[PageModel::IDENTIFIER] === 'test_custom_layout_page_2') {
                $page2Data = $pageData;
            }
        }
        $this->assertNotEmpty($page1Data);
        $this->assertNotEmpty($page2Data);
        $this->assertEquals('_existing_', $page1Data['layout_update_selected']);
        $this->assertEquals(null, $page2Data['layout_update_selected']);
    }
}
