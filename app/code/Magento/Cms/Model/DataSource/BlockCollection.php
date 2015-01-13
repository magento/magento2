<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\DataSource;

use Magento\Framework\Data\CollectionDataSourceInterface;
use Magento\Cms\Model\Resource\BlockCriteria;
use Magento\Cms\Model\BlockRepository;

/**
 * CMS block collection data source
 *
 * Class BlockCollection
 */
class BlockCollection extends BlockCriteria implements CollectionDataSourceInterface
{
    /**
     * @var BlockRepository
     */
    protected $repository;

    /**
     * @param BlockRepository $repository
     * @param string $mapper
     */
    public function __construct(BlockRepository $repository, $mapper = '')
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
     * @return \Magento\Cms\Model\Resource\Block\Collection
     */
    public function getResultCollection()
    {
        return $this->repository->getList($this);
    }
}
