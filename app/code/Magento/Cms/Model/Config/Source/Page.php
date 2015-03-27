<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Config\Source;

/**
 * Class Page
 */
class Page implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    protected $pageRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $pageCriteriaBuilder;

    /**
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $pageCriteriaBuilder
     */
    public function __construct(
        \Magento\Cms\Model\PageRepository $pageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $pageCriteriaBuilder
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageCriteriaBuilder = $pageCriteriaBuilder;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->pageRepository->getList($this->pageCriteriaBuilder->create())->toOptionIdArray();
        }
        return $this->options;
    }
}
