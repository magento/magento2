<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Fixture;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class CmsPage implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'title' => 'CMS Test Page',
        'identifier' => 'cms-test-page',
        'stores' => [0],
        'is_active' => 0, // Key difference - disabled by default
        'content' => '<h1>This is a disabled CMS page</h1><p>This page should not be accessible via GraphQL.</p>',
        'content_heading' => 'Disabled Page Heading',
        'page_layout' => '1column',
        'meta_title' => 'Disabled Page Meta Title',
        'meta_keywords' => 'disabled, cms, page, test, graphql',
        'meta_description' => 'This is a disabled CMS page used for GraphQL error handling tests'
    ];

    /**
     * @param PageFactory $pageFactory
     * @param PageRepositoryInterface $pageRepository
     * @param DataMerger $dataMerger
     */
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly DataMerger $dataMerger,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);

        $page = $this->pageFactory->create();
        $page->setTitle($data['title'])
            ->setIdentifier($data['identifier'])
            ->setStores($data['stores'])
            ->setIsActive($data['is_active'])
            ->setContent($data['content'])
            ->setContentHeading($data['content_heading'])
            ->setPageLayout($data['page_layout'])
            ->setMetaTitle($data['meta_title'])
            ->setMetaKeywords($data['meta_keywords'])
            ->setMetaDescription($data['meta_description']);

        $this->pageRepository->save($page);

        return $page;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->pageRepository->delete($data);
    }
}
