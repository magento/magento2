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
 * Eav Form Fieldset Resource Collection
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Resource\Form\Fieldset;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Store scope ID
     *
     * @var int
     */
    protected $_storeId;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $resource);
    }

    /**
     * Initialize collection model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Form\Fieldset', 'Magento\Eav\Model\Resource\Form\Fieldset');
    }

    /**
     * Add Form Type filter to collection
     *
     * @param \Magento\Eav\Model\Form\Type|int $type
     * @return \Magento\Eav\Model\Resource\Form\Fieldset\Collection
     */
    public function addTypeFilter($type)
    {
        if ($type instanceof \Magento\Eav\Model\Form\Type) {
            $type = $type->getId();
        }

        return $this->addFieldToFilter('type_id', $type);
    }

    /**
     * Set order by fieldset sort order
     *
     * @return \Magento\Eav\Model\Resource\Form\Fieldset\Collection
     */
    public function setSortOrder()
    {
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Retrieve label store scope
     *
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Set store scope ID
     *
     * @param int $storeId
     * @return \Magento\Eav\Model\Resource\Form\Fieldset\Collection
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Initialize select object
     *
     * @return \Magento\Eav\Model\Resource\Form\Fieldset\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->join(
            array('default_label' => $this->getTable('eav_form_fieldset_label')),
            'main_table.fieldset_id = default_label.fieldset_id AND default_label.store_id = 0',
            array());
        if ($this->getStoreId() == 0) {
            $select->columns('label', 'default_label');
        } else {
            $labelExpr = $select->getAdapter()
                ->getIfNullSql('store_label.label', 'default_label.label');
            $joinCondition = $this->getConnection()
                ->quoteInto(
                    'main_table.fieldset_id = store_label.fieldset_id AND store_label.store_id = ?', 
                    (int)$this->getStoreId());
            $select->joinLeft(
                array('store_label' => $this->getTable('eav_form_fieldset_label')),
                $joinCondition,
                array('label' => $labelExpr)
            );
        }

        return $this;
    }
}
