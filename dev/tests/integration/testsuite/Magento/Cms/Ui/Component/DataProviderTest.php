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
 * @magentoDbIsolation enabled
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
     * @magentoDataFixture Magento/Cms/_files/pages.php
     *
     * @return void
     */
    public function testPageFilteringByTitlePart(): void
    {
        $this->request->setParams(['search' => 'Cms Page 1']);
        $data = $this->getComponentProvidedData('cms_page_listing');
        $items = $data['items'];
        $this->assertCount(1, $items);
        $this->assertEquals('page100', reset($items)[PageInterface::IDENTIFIER]);
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/blocks.php
     *
     * @return void
     */
    public function testBlockFilteringByTitlePart(): void
    {
        $this->request->setParams(['search' => 'Enabled CMS Block']);
        $data = $this->getComponentProvidedData('cms_block_listing');
        $items = $data['items'];
        $this->assertCount(1, $items);
        $this->assertEquals('enabled_block', reset($items)[BlockInterface::IDENTIFIER]);
    }

    /**
     * Call prepare method in the child components
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareChildComponents(UiComponentInterface $component): void
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
