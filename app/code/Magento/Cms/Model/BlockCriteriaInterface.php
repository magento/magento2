<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Model;

/**
 * Interface BlockCriteriaInterface
 */
interface BlockCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return void
     */
    public function setFirstStoreFlag($flag = false);

    /**
     * Add filter by store
     *
     * @param int $storeId
     * @param bool $withAdmin
     * @return void
     */
    public function addStoreFilter($storeId, $withAdmin = true);
}
