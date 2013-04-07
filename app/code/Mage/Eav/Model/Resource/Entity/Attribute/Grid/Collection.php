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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Eav Resource Attribute Set Collection
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Resource_Entity_Attribute_Grid_Collection
    extends Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
{
    /**
     * @var Mage_Core_Model_Registry
     */
    protected $_registryManager;

    /**
     * @param Mage_Core_Model_Resource_Db_Abstract $resource
     * @param Mage_Core_Model_Registry $registryManager
     */
    public function __construct(
        Mage_Core_Model_Registry $registryManager, Mage_Core_Model_Resource_Db_Abstract $resource = null
    ) {
        $this->_registryManager = $registryManager;
        parent::__construct($resource);
    }

    /**
     *  Add filter by entity type id to collection
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract|Mage_Eav_Model_Resource_Entity_Attribute_Grid_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->setEntityTypeFilter($this->_registryManager->registry('entityType'));
        return $this;
    }
}
