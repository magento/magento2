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
 * @package     Magento_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Entity attribute option collection
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Resource\Entity\Attribute\Option;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Option value table
     *
     * @var string
     */
    protected $_optionValueTable;

    /**
     * @var \Magento\Core\Model\Resource
     */
    protected $_coreResource;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Core\Model\Resource $coreResource
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Core\Model\Resource $coreResource,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreResource = $coreResource;
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $resource);
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Entity\Attribute\Option', 'Magento\Eav\Model\Resource\Entity\Attribute\Option');
        $this->_optionValueTable = $this->_coreResource->getTableName('eav_attribute_option_value');
    }

    /**
     * Set attribute filter
     *
     * @param int $setId
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection
     */
    public function setAttributeFilter($setId)
    {
        return $this->addFieldToFilter('attribute_id', $setId);
    }


    /**
     * Add store filter to collection
     *
     * @param int $storeId
     * @param boolean $useDefaultValue
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection
     */
    public function setStoreFilter($storeId = null, $useDefaultValue = true)
    {
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $adapter = $this->getConnection();

        $joinCondition = $adapter->quoteInto('tsv.option_id = main_table.option_id AND tsv.store_id = ?', $storeId);

        if ($useDefaultValue) {
            $this->getSelect()
                ->join(
                    array('tdv' => $this->_optionValueTable),
                    'tdv.option_id = main_table.option_id',
                    array('default_value' => 'value'))
                ->joinLeft(
                    array('tsv' => $this->_optionValueTable),
                    $joinCondition,
                    array(
                        'store_default_value' => 'value',
                        'value'               => $adapter->getCheckSql('tsv.value_id > 0', 'tsv.value', 'tdv.value')
                    ))
                ->where('tdv.store_id = ?', 0);
        } else {
            $this->getSelect()
                ->joinLeft(
                    array('tsv' => $this->_optionValueTable),
                    $joinCondition,
                    'value')
                ->where('tsv.store_id = ?', $storeId);
        }

        $this->setOrder('value', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Add option id(s) frilter to collection
     *
     * @param int|array $optionId
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection
     */
    public function setIdFilter($optionId)
    {
        return $this->addFieldToFilter('option_id', array('in' => $optionId));
    }

    /**
     * Convert collection items to select options array
     *
     * @param string $valueKey
     * @return array
     */
    public function toOptionArray($valueKey = 'value')
    {
        return $this->_toOptionArray('option_id', $valueKey);
    }

    /**
     * Set order by position or alphabetically by values in admin
     *
     * @param string $dir direction
     * @param boolean $sortAlpha sort alphabetically by values in admin
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection
     */
    public function setPositionOrder($dir = self::SORT_ORDER_ASC, $sortAlpha = false)
    {
        $this->setOrder('main_table.sort_order', $dir);
        // sort alphabetically by values in admin
        if ($sortAlpha) {
            $this->getSelect()
                ->joinLeft(
                    array('sort_alpha_value' => $this->_optionValueTable),
                    'sort_alpha_value.option_id = main_table.option_id AND sort_alpha_value.store_id = 0',
                    array('value'));
            $this->setOrder('sort_alpha_value.value', $dir);
        }

        return $this;
    }
}
