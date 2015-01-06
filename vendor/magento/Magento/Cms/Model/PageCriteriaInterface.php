<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Model;

/**
 * Interface PageCriteriaInterface
 */
interface PageCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
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
     * @param int|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    public function addStoreFilter($store, $withAdmin = true);
}
