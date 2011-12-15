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
 * @category    Mage
 * @package     Mage_Log
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Resource model for commands, executed in shell
 *
 * @category    Mage
 * @package     Mage_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Log_Model_Resource_Shell
{
    /**
     * Retrieves information about log tables
     *
     * @return array
     */
    public function getTablesInfo()
    {
        $tables = array(
            'log_customer',
            'log_visitor',
            'log_visitor_info',
            'log_url_table',
            'log_url_info_table',
            'log_quote_table',
            'reports_viewed_product_index',
            'reports_compared_product_index',
            'reports_event',
            'catalog_compare_item'
        );

        $resHelper = Mage::getResourceHelper('Mage_Log');
        $result = array();
        $resource = Mage::getSingleton('Mage_Core_Model_Resource');
        foreach ($tables as $table) {
            $info = $resHelper->getTableInfo($resource->getTableName($table));
            if (!$info) {
                continue;
            }
            $result[] = $info;
        }

        return $result;
    }
}
