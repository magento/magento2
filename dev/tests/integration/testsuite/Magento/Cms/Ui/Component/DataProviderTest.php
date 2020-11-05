<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Ui\Component;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks Cms UI component data provider behaviour
 *
 * @magentoAppArea adminhtml
 */
class DataProviderTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var UiComponentFactory */
    private $componentFactory;

    /** @var RequestInterface */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->componentFactory = $this->objectManager->get(UiComponentFactory::class);
    }

    /**
     * @dataProvider pageFilterDataProvider
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     *
     * @param array $filter
     * @param string $expectedPage
     * @return void
     */
    public function testPageFiltering(array $filter, string $expectedPage): void
    {
        $this->request->setParams(['filters' => $filter]);
        $data = $this->getComponentProvidedData('cms_page_listing');
        $this->assertCount(1, $data['items']);
        $this->assertEquals(reset($data['items'])[PageInterface::IDENTIFIER], $expectedPage);
    }

    /**
     * @return array
     */
    public function pageFilterDataProvider(): array
    {
        return [
            'partial_title_filter' => [
                'filter' => ['title' => 'Cms Page 1'],
                'expected_item' => 'page100',
            ],
            'multiple_filter' => [
                'filter' => [
                    'title' => 'Cms Page',
                    'meta_title' => 'Cms Meta title for Blank page',
                ],
                'expected_item' => 'page_design_blank',
            ],
        ];
    }

    /**
     * @dataProvider blockFilterDataProvider
     *
     * @magentoDataFixture Magento/Cms/_files/blocks.php
     *
     * @return void
     */
    public function testBlockFiltering(array $filter, string $expectedBlock): void
    {
        $this->request->setParams(['filters' => $filter]);
        $data = $this->getComponentProvidedData('cms_block_listing');
        $this->assertCount(1, $data['items']);
        $this->assertEquals(reset($data['items'])[BlockInterface::IDENTIFIER], $expectedBlock);
    }

    /**
     * @return array
     */
    public function blockFilterDataProvider(): array
    {
        return [
            'partial_title_filter' => [
                'filter' => ['title' => 'Enabled CMS Block'],
                'expected_item' => 'enabled_block',
            ],
            'multiple_filter' => [
                'filter' => [
                    'title' => 'CMS Block Title',
                    'is_active' => [0],
                ],
                'expected_item' => 'disabled_block',
            ],
        ];
    }

    /**
     * Call prepare method in the child components
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareChildComponents(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareChildComponents($child);
        }

        $component->prepare();
    }

    /**
     * Get component provided data
     *
     * @param string $namespace
     * @return array
     */
    private function getComponentProvidedData(string $namespace): array
    {
        $component = $this->componentFactory->create($namespace);
        $this->prepareChildComponents($component);

        return $component->getContext()->getDataProvider()->getData();
    }
}
