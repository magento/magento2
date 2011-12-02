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
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Google Optimizer Category model
 *
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Model_Code_Category extends Mage_GoogleOptimizer_Model_Code
{

    protected $_entityType = 'category';

    /**
     * Removing scripts assigned to entity
     *
     * @param integer $storeId
     * @return Mage_GoogleOptimizer_Model_Code
     */
    public function deleteScripts($storeId)
    {
        $category = $this->getEntity();
        if ($category) {
            /**
             * We need check category children ids
             */
            $ids = $category->getDeletedChildrenIds();
            if (is_array($ids)) {
                $ids[] = $category->getId();
            } else {
                $ids = array($category->getId());
            }
            $this->setEntityIds($ids);
        }
        return parent::deleteScripts($storeId);
    }

}
