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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * History compaction strategies interface
 */
interface Mage_DesignEditor_Model_History_CompactInterface
{
    /**
     * Set change collection
     *
     * @param Mage_DesignEditor_Model_Change_Collection $collection
     * @return Mage_DesignEditor_Model_History_CompactInterface
     */
    public function setChangesCollection(Mage_DesignEditor_Model_Change_Collection $collection);

    /**
     * Get change collection
     *
     * @return Mage_DesignEditor_Model_Change_Collection
     */
    public function getChangesCollection();

    /**
     * Signature of compact method to implement in subclasses
     *
     * @param Mage_DesignEditor_Model_Change_Collection|null $collection
     * @throws Magento_Exception
     * @return Mage_DesignEditor_Model_History_CompactInterface
     */
    public function compact($collection = null);
}
