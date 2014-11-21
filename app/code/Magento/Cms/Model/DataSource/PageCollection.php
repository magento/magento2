<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Model\DataSource;

use Magento\Framework\Data\AbstractCriteria;
use Magento\Framework\Data\CollectionDataSourceInterface;

/**
 * CMS page collection data source
 *
 * Class PageCollection
 */
class PageCollection extends AbstractCriteria implements CollectionDataSourceInterface
{
    /**
     * @var \Magento\Cms\Api\PageCriteriaInterface
     */
    protected $criteria;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    protected $repository;

    /**
     * @param \Magento\Cms\Api\PageCriteriaInterface $criteria
     * @param \Magento\Cms\Api\PageRepositoryInterface $repository
     */
    public function __construct(
        \Magento\Cms\Api\PageCriteriaInterface $criteria,
        \Magento\Cms\Api\PageRepositoryInterface $repository
    ) {
        $this->criteria = $criteria;
        $this->repository = $repository;
        $this->criteria->setFirstStoreFlag(true);
    }

    /**
     * @inheritdoc
     */
    public function addFilter($name, $field, $condition = null, $type = 'public')
    {
        if ($field === 'store_id') {
            $this->criteria->addStoreFilter($condition, false);
        } else {
            $this->criteria->addFilter($name, $field, $condition, $type);
        }
    }

    /**
     * @return \Magento\Cms\Api\Data\PageCollectionInterface
     */
    public function getResultCollection()
    {
        return $this->repository->getList($this->criteria);
    }

    /**
     * Add Criteria object
     *
     * @param \Magento\Cms\Api\PageCriteriaInterface $criteria
     * @return void
     */
    public function addCriteria(\Magento\Cms\Api\PageCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
    }
}
