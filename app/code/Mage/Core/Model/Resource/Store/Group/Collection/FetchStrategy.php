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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Custom fetch strategy for the store group collection
 */
class Mage_Core_Model_Resource_Store_Group_Collection_FetchStrategy
    extends Varien_Data_Collection_Db_FetchStrategy_Cache
{
    /**
     * Constructor
     *
     * @param Magento_Cache_FrontendInterface $cache
     * @param Varien_Data_Collection_Db_FetchStrategyInterface $fetchStrategy
     */
    public function __construct(
        Magento_Cache_FrontendInterface $cache,
        Varien_Data_Collection_Db_FetchStrategyInterface $fetchStrategy
    ) {
        parent::__construct($cache, $fetchStrategy, 'app_', array(Mage_Core_Model_Store_Group::CACHE_TAG), false);
    }
}
