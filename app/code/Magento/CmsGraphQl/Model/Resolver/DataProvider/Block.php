<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\DataProvider;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Cms block data provider
 */
class Block
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;
    
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param BlockRepositoryInterface $blockRepository
     * @param FilterEmulate $widgetFilter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        FilterEmulate $widgetFilter,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        StoreManagerInterface $storeManager = null
    ) {
        $this->blockRepository = $blockRepository;
        $this->widgetFilter = $widgetFilter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: \Magento\Framework
            \App\ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $this->storeManager = $storeManager ?: \Magento\Framework
            \App\ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Get block data
     *
     * @param string $blockIdentifier
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(string $blockIdentifier): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $blockIdentifier, 'eq')
            ->addFilter('store_id', $this->storeManager->getStore()->getId(), 'eq')
            ->addFilter('is_active', true, 'eq')->create();
        $block = current($this->blockRepository->getList($searchCriteria)->getItems());

        if (empty($block)) {
            throw new NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $blockIdentifier)
            );
        }

        $renderedContent = $this->widgetFilter->filterDirective($block->getContent());

        $blockData = [
            BlockInterface::BLOCK_ID => $block->getId(),
            BlockInterface::IDENTIFIER => $block->getIdentifier(),
            BlockInterface::TITLE => $block->getTitle(),
            BlockInterface::CONTENT => $renderedContent,
        ];
        return $blockData;
    }
}
