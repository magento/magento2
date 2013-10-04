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
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog search query resource model
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Model\Resource;

class Query extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Date
     *
     * @var \Magento\Core\Model\Date
     */
    protected $_date;

    /**
     * Class constructor
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Core\Model\Date $date
     */
    public function __construct(
        \Magento\Core\Model\Resource $resource,
        \Magento\Core\Model\Date $date
    ) {
        $this->_date = $date;
        parent::__construct($resource);
    }

    /**
     * Init resource data
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_query', 'query_id');
    }

    /**
     * Custom load model by search query string
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @param string $value
     * @return \Magento\CatalogSearch\Model\Resource\Query
     */
    public function loadByQuery(\Magento\Core\Model\AbstractModel $object, $value)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('synonym_for=? OR query_text=?', $value)
            ->where('store_id=?', $object->getStoreId())
            ->order('synonym_for ASC')
            ->limit(1);
        $data = $this->_getReadAdapter()->fetchRow($select);
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;
    }

    /**
     * Custom load model only by query text (skip synonym for)
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @param string $value
     * @return \Magento\CatalogSearch\Model\Resource\Query
     */
    public function loadByQueryText(\Magento\Core\Model\AbstractModel $object, $value)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('query_text = ?', $value)
            ->where('store_id = ?', $object->getStoreId())
            ->limit(1);
        $data = $this->_getReadAdapter()->fetchRow($select);
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;
    }

    /**
     * Loading string as a value or regular numeric
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @param int|string $value
     * @param null|string $field
     * @return \Magento\CatalogSearch\Model\Resource\Query
     */
    public function load(\Magento\Core\Model\AbstractModel $object, $value, $field = null)
    {
        if (is_numeric($value)) {
            return parent::load($object, $value);
        } else {
            $this->loadByQuery($object, $value);
        }
        return $this;
    }

    /**
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\CatalogSearch\Model\Resource\Query
     */
    public function _beforeSave(\Magento\Core\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->formatDate($this->_date->gmtTimestamp()));
        return $this;
    }
}
