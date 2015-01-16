<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\DataSource;

use Magento\Cms\Model\Resource\PageCriteria;
use Magento\Framework\Data\CollectionDataSourceInterface;

/**
 * CMS page collection data source
 *
 * Class PageCollection
 */
class PageCollection extends PageCriteria implements CollectionDataSourceInterface
{
    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    protected $repository;

    /**
     * @param \Magento\Cms\Model\PageRepository $repository
     * @param string $mapper
     */
    public function __construct(\Magento\Cms\Model\PageRepository $repository, $mapper = '')
    {
        $this->repository = $repository;
        $this->setFirstStoreFlag(true);
        parent::__construct($mapper);
    }

    /**
     * @inheritdoc
     */
    public function addFilter($name, $field, $condition = null, $type = 'public')
    {
        if ($field === 'store_id') {
            $this->addStoreFilter($condition, false);
        } else {
            parent::addFilter($name, $field, $condition, $type);
        }
    }

    /**
     * @return \Magento\Cms\Model\Resource\Page\Collection
     */
    public function getResultCollection()
    {
        return $this->repository->getList($this);
    }

    /**
     * Add Criteria object
     *
     * @param \Magento\Cms\Model\Resource\PageCriteria $criteria
     * @return void
     */
    public function addCriteria(\Magento\Cms\Model\Resource\PageCriteria $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
    }
}
