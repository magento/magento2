<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Model\Resource;

use Magento\Cms\Model\PageCriteriaInterface;

/**
 * Class PageCriteria
 */
class PageCriteria extends CmsAbstractCriteria implements PageCriteriaInterface
{
    /**
     * @param string $mapper
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: 'Magento\Cms\Model\Resource\PageCriteriaMapper';
    }

    /**
     * @inheritdoc
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->data['first_store_flag'] = $flag;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->data['store_filter'] = [$store, $withAdmin];
        return true;
    }

    /**
     * Add Criteria object
     *
     * @param \Magento\Cms\Model\Resource\PageCriteria $criteria
     * @return bool
     */
    public function addCriteria(\Magento\Cms\Model\Resource\PageCriteria $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
        return true;
    }
}
