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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Model\Resource\Advanced;

use Magento\Core\Exception;

/**
 * Collection Advanced
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Catalog\Model\Resource\Product\Collection
{
    /**
     * Date
     *
     * @var \Magento\Core\Model\Date
     */
    protected $_date;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Core\Model\Date $date
     * @param \Zend_Db_Adapter_Abstract $connection
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Core\Model\Date $date,
        $connection = null
    ) {
        $this->_date = $date;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $catalogData,
            $catalogProductFlatState,
            $coreStoreConfig,
            $productOptionFactory,
            $catalogUrl,
            $locale,
            $customerSession,
            $dateTime,
            $connection
        );
    }

    /**
     * Add not indexable fields to search
     *
     * @param array $fields
     * @return $this
     * @throws Exception
     */
    public function addFieldsToFilter($fields)
    {
        if ($fields) {
            $previousSelect = null;
            $conn = $this->getConnection();
            foreach ($fields as $table => $conditions) {
                foreach ($conditions as $attributeId => $conditionValue) {
                    $select = $conn->select();
                    $select->from(array('t1' => $table), 'entity_id');
                    $conditionData = array();

                    if (!is_numeric($attributeId)) {
                        $field = 't1.'.$attributeId;
                    }
                    else {
                        $storeId = $this->getStoreId();
                        $onCondition = 't1.entity_id = t2.entity_id'
                                . ' AND t1.attribute_id = t2.attribute_id'
                                . ' AND t2.store_id=?';

                        $select->joinLeft(
                            array('t2' => $table),
                            $conn->quoteInto($onCondition, $storeId),
                            array()
                        );
                        $select->where('t1.store_id = ?', 0);
                        $select->where('t1.attribute_id = ?', $attributeId);

                        if (array_key_exists('price_index', $this->getSelect()->getPart(\Magento\DB\Select::FROM))) {
                            $select->where('t1.entity_id = price_index.entity_id');
                        }

                        $field = $this->getConnection()->getCheckSql('t2.value_id>0', 't2.value', 't1.value');

                    }

                    if (is_array($conditionValue)) {
                        if (isset($conditionValue['in'])){
                            $conditionData[] = array('in' => $conditionValue['in']);
                        }
                        elseif (isset($conditionValue['in_set'])) {
                            $conditionParts = array();
                            foreach ($conditionValue['in_set'] as $value) {
                                $conditionParts[] = array('finset' => $value);
                            }
                            $conditionData[] = $conditionParts;
                        }
                        elseif (isset($conditionValue['like'])) {
                            $conditionData[] = array ('like' => $conditionValue['like']);
                        }
                        elseif (isset($conditionValue['from']) && isset($conditionValue['to'])) {
                            $invalidDateMessage = __('Please specify correct data.');
                            if ($conditionValue['from']) {
                                if (!\Zend_Date::isDate($conditionValue['from'])) {
                                    throw new Exception($invalidDateMessage);
                                }
                                if (!is_numeric($conditionValue['from'])){
                                    $conditionValue['from'] = $this->_date->gmtDate(null, $conditionValue['from']);
                                    if (!$conditionValue['from']) {
                                        $conditionValue['from'] = $this->_date->gmtDate();
                                    }
                                }
                                $conditionData[] = array('gteq' => $conditionValue['from']);
                            }
                            if ($conditionValue['to']) {
                                if (!\Zend_Date::isDate($conditionValue['to'])) {
                                    throw new Exception($invalidDateMessage);
                                }
                                if (!is_numeric($conditionValue['to'])){
                                    $conditionValue['to'] = $this->_date->gmtDate(null, $conditionValue['to']);
                                    if (!$conditionValue['to']) {
                                        $conditionValue['to'] = $this->_date->gmtDate();
                                    }
                                }
                                $conditionData[] = array('lteq' => $conditionValue['to']);
                            }
                        }
                    } else {
                        $conditionData[] = array('eq' => $conditionValue);
                    }


                    foreach ($conditionData as $data) {
                        $select->where($conn->prepareSqlCondition($field, $data));
                    }

                    if (!is_null($previousSelect)) {
                        $select->where('t1.entity_id IN (?)', new \Zend_Db_Expr($previousSelect));
                    }
                    $previousSelect = $select;
                }
            }
            $this->addFieldToFilter('entity_id', array('in' => new \Zend_Db_Expr($select)));
        }

        return $this;
    }
}
