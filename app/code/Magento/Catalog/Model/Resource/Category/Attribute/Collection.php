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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog category EAV additional attribute resource collection
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Resource\Category\Attribute;

class Collection
    extends \Magento\Eav\Model\Resource\Entity\Attribute\Collection
{
    /**
     * Entity factory
     *
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $_eavEntityFactory;

    /**
     * Construct
     *
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_eavEntityFactory = $eavEntityFactory;
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $resource);
    }

    /**
     * Main select object initialization.
     * Joins catalog/eav_attribute table
     *
     * @return \Magento\Catalog\Model\Resource\Category\Attribute\Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()))
            ->where(
                'main_table.entity_type_id=?',
                $this->_eavEntityFactory->create()->setType(\Magento\Catalog\Model\Category::ENTITY)->getTypeId()
            )->join(
                array('additional_table' => $this->getTable('catalog_eav_attribute')),
                'additional_table.attribute_id = main_table.attribute_id'
            );
        return $this;
    }

    /**
     * Specify attribute entity type filter
     *
     * @param int $typeId
     * @return \Magento\Catalog\Model\Resource\Category\Attribute\Collection
     */
    public function setEntityTypeFilter($typeId)
    {
        return $this;
    }
}
