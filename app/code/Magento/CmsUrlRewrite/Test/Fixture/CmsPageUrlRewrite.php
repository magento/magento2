<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Fixture;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResourceModel;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDataModel;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;

class CmsPageUrlRewrite extends UrlRewrite
{
    private const DEFAULT_DATA = [
        UrlRewriteDataModel::ENTITY_TYPE => 'cms-page',
        UrlRewriteDataModel::REDIRECT_TYPE => 0,
        UrlRewriteDataModel::STORE_ID => 1
    ];

    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @var CmsPageUrlPathGenerator
     */
    private CmsPageUrlPathGenerator $cmsPageUrlPathGenerator;

    /**
     * @inheritDoc
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteResourceModel $urlRewriteResourceModel,
        ProcessorInterface $dataProcessor,
        PageRepositoryInterface $pageRepository,
        CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
    ) {
        parent::__construct($urlRewriteFactory, $urlRewriteResourceModel, $dataProcessor);
        $this->pageRepository = $pageRepository;
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply($this->prepareData($data));
    }

    /**
     * Prepare default data
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $page = $this->pageRepository->getById(
            $data[UrlRewriteDataModel::ENTITY_ID]
        );
        if (!isset($data[UrlRewriteDataModel::TARGET_PATH])) {
            if ($data[UrlRewriteDataModel::REDIRECT_TYPE]) {
                $data[UrlRewriteDataModel::TARGET_PATH] = $this->cmsPageUrlPathGenerator->getUrlPath($page);
            } else {
                $data[UrlRewriteDataModel::TARGET_PATH] = $this->cmsPageUrlPathGenerator->getCanonicalUrlPath($page);
            }
        }
        return $data;
    }
}
