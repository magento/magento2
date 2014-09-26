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
namespace Magento\CatalogUrlRewrite\Model\Resource\Category;

use Magento\Framework\Model\Resource\Db\AbstractDb;

class Product extends AbstractDb
{
    /**
     * Product/Category relation table name
     */
    const TABLE_NAME = 'catalog_url_rewrite_product_category';

    /**
     * Chunk for mass insert
     */
    const CHUNK_SIZE = 100;

    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'url_rewrite_id');
    }

    /**
     * @param array $insertData
     * @return int
     */
    public function saveMultiple(array $insertData)
    {
        $write = $this->_getWriteAdapter();
        if (sizeof($insertData) <= self::CHUNK_SIZE) {
            return $write->insertMultiple($this->getTable(self::TABLE_NAME), $insertData);
        }
        $data = array_chunk($insertData, self::CHUNK_SIZE);
        $totalCount = 0;
        foreach ($data as $insertData) {
            $totalCount += $write->insertMultiple($this->getTable(self::TABLE_NAME), $insertData);
        }
        return $totalCount;
    }

    /**
     * @param array $removeData
     * @return int
     */
    public function removeMultiple(array $removeData)
    {
        $write = $this->_getWriteAdapter();
        return $write->delete($this->getTable(self::TABLE_NAME), array('url_rewrite_id in (?)' => $removeData));
    }
}
