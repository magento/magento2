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
     * @var \Magento\Cms\Model\Resource\PageCriteria
     */
    protected $pageCriteriaFactory;

    /**
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     * @param \Magento\Cms\Model\Resource\PageCriteriaFactory $pageCriteriaFactory
     */
    public function __construct(
        \Magento\Cms\Model\PageRepository $pageRepository,
        \Magento\Cms\Model\Resource\PageCriteriaFactory $pageCriteriaFactory
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageCriteriaFactory = $pageCriteriaFactory;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->pageRepository->getList($this->pageCriteriaFactory->create())->toOptionIdArray();
        }
        return $this->options;
    }
}
