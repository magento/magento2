<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Fixture;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Programmatic CMS page test fixture. Uses the repository (not the web-API service layer) so
 * all {@see Page} fields such as <code>is_active</code> and store assignments are supported.
 */
class CmsPage implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        PageInterface::IDENTIFIER => 'page%uniqid%',
        PageInterface::TITLE => 'Page%uniqid%',
        PageInterface::PAGE_LAYOUT => '1column',
        PageInterface::META_TITLE => 'Meta%uniqid%',
        PageInterface::META_KEYWORDS => 'keywords%uniqid%',
        PageInterface::META_DESCRIPTION => 'Description%uniqid%',
        PageInterface::CONTENT_HEADING => 'Heading%uniqid%',
        PageInterface::CONTENT => '<h1>Content%uniqid%</h1>',
        PageInterface::IS_ACTIVE => 1,
    ];

    public function __construct(
        private readonly ProcessorInterface $dataProcessor,
        private readonly PageFactory $pageFactory,
        private readonly PageRepositoryInterface $pageRepository
    ) {
    }

    /**
     * @inheritdoc
     * @param array $data Per {@see CmsPage::DEFAULT_DATA} plus optional <code>stores</code> (default <code>[0]</code>).
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data));
        $storeIds = $data['stores'] ?? [0];
        unset($data['stores']);
        $page = $this->pageFactory->create();
        $page->addData($data);
        $page->setData('stores', $storeIds);
        $this->pageRepository->save($page);

        return $page;
    }

    public function revert(DataObject $data): void
    {
        $this->pageRepository->delete($data);
    }
}
