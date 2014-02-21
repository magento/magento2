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
 * @category    Magento
 * @package     Magento_Log
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Resource model for commands, executed in shell
 *
 * @category    Magento
 * @package     Magento_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model\Resource;

class Shell
{
    /**
     * @var \Magento\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Core\Model\Resource\HelperFactory
     */
    protected $_helperPool;

    /**
     * @param \Magento\Core\Model\Resource\HelperPool $helperPool
     * @param \Magento\App\Resource $resource
     */
    public function __construct(
        \Magento\Core\Model\Resource\HelperPool $helperPool,
        \Magento\App\Resource $resource
    ) {
        $this->_helperPool = $helperPool;
        $this->_resource = $resource;
    }

    /**
     * Retrieves information about log tables
     *
     * @return string[]
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

        $resHelper = $this->_helperPool->get('Magento_Log');
        $result = array();
        foreach ($tables as $table) {
            $info = $resHelper->getTableInfo($this->_resource->getTableName($table));
            if (!$info) {
                continue;
            }
            $result[] = $info;
        }

        return $result;
    }
}
