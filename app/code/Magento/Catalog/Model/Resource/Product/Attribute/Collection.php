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
namespace Magento\Catalog\Model\Resource\Product\Attribute;

/**
 * Catalog product EAV additional attribute resource collection
 */
class Collection extends \Magento\Eav\Model\Resource\Entity\Attribute\Collection
{
    /**
     * Entity factory1
     *
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $_eavEntityFactory;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_eavEntityFactory = $eavEntityFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Eav\Attribute', 'Magento\Eav\Model\Resource\Entity\Attribute');
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $entityTypeId = (int)$this->_eavEntityFactory->create()->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getTypeId();
        $columns = $this->getConnection()->describeTable($this->getResource()->getMainTable());
        unset($columns['attribute_id']);
        $retColumns = array();
        foreach ($columns as $labelColumn => $columnData) {
            $retColumns[$labelColumn] = $labelColumn;
            if ($columnData['DATA_TYPE'] == \Magento\Framework\DB\Ddl\Table::TYPE_TEXT) {
                $retColumns[$labelColumn] = 'main_table.' . $labelColumn;
            }
        }
        $this->getSelect()->from(
            array('main_table' => $this->getResource()->getMainTable()),
            $retColumns
        )->join(
            array('additional_table' => $this->getTable('catalog_eav_attribute')),
            'additional_table.attribute_id = main_table.attribute_id'
        )->where(
            'main_table.entity_type_id = ?',
            $entityTypeId
        );
        return $this;
    }

    /**
     * Specify attribute entity type filter.
     * Entity type is defined.
     *
     * @param  int $typeId
     * @return $this
     */
    public function setEntityTypeFilter($typeId)
    {
        return $this;
    }

    /**
     * Return array of fields to load attribute values
     *
     * @return string[]
     */
    protected function _getLoadDataFields()
    {
        $fields = array_merge(
            parent::_getLoadDataFields(),
            array(
                'additional_table.is_global',
                'additional_table.is_html_allowed_on_front',
                'additional_table.is_wysiwyg_enabled'
            )
        );

        return $fields;
    }

    /**
     * Remove price from attribute list
     *
     * @return $this
     */
    public function removePriceFilter()
    {
        return $this->addFieldToFilter('main_table.attribute_code', array('neq' => 'price'));
    }

    /**
     * Specify "is_visible_in_advanced_search" filter
     *
     * @return $this
     */
    public function addDisplayInAdvancedSearchFilter()
    {
        return $this->addFieldToFilter('additional_table.is_visible_in_advanced_search', 1);
    }

    /**
     * Specify "is_filterable" filter
     *
     * @return $this
     */
    public function addIsFilterableFilter()
    {
        return $this->addFieldToFilter('additional_table.is_filterable', array('gt' => 0));
    }

    /**
     * Add filterable in search filter
     *
     * @return $this
     */
    public function addIsFilterableInSearchFilter()
    {
        return $this->addFieldToFilter('additional_table.is_filterable_in_search', array('gt' => 0));
    }

    /**
     * Specify filter by "is_visible" field
     *
     * @return $this
     */
    public function addVisibleFilter()
    {
        return $this->addFieldToFilter('additional_table.is_visible', 1);
    }

    /**
     * Specify "is_searchable" filter
     *
     * @return $this
     */
    public function addIsSearchableFilter()
    {
        return $this->addFieldToFilter('additional_table.is_searchable', 1);
    }

    /**
     * Specify filter for attributes that have to be indexed
     *
     * @param bool $addRequiredCodes
     * @return $this
     */
    public function addToIndexFilter($addRequiredCodes = false)
    {
        $conditions = array(
            'additional_table.is_searchable = 1',
            'additional_table.is_visible_in_advanced_search = 1',
            'additional_table.is_filterable > 0',
            'additional_table.is_filterable_in_search = 1',
            'additional_table.used_for_sort_by = 1'
        );

        if ($addRequiredCodes) {
            $conditions[] = $this->getConnection()->quoteInto(
                'main_table.attribute_code IN (?)',
                array('status', 'visibility')
            );
        }

        $this->getSelect()->where(sprintf('(%s)', implode(' OR ', $conditions)));

        return $this;
    }

    /**
     * Specify filter for attributes used in quick search
     *
     * @return $this
     */
    public function addSearchableAttributeFilter()
    {
        $this->getSelect()->where(
            'additional_table.is_searchable = 1 OR ' . $this->getConnection()->quoteInto(
                'main_table.attribute_code IN (?)',
                array('status', 'visibility')
            )
        );

        return $this;
    }
}
