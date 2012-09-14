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
 * Compaction model abstract
 */
abstract class Mage_DesignEditor_Model_History_CompactAbstract
    implements Mage_DesignEditor_Model_History_CompactInterface
{
    /**
     * Changes collection
     *
     * @var Mage_DesignEditor_Model_Change_Collection
     */
    protected $_changesCollection;

    /**
     * Set change collection
     *
     * @param Mage_DesignEditor_Model_Change_Collection $collection
     * @return Mage_DesignEditor_Model_History_Compact_Layout
     */
    public function setChangesCollection(Mage_DesignEditor_Model_Change_Collection $collection)
    {
        $this->_changesCollection = $collection;
        return $this;
    }

    /**
     * Get change collection
     *
     * @return Mage_DesignEditor_Model_Change_Collection
     */
    public function getChangesCollection()
    {
        return $this->_changesCollection;
    }

    /**
     * Signature of compact method to implement in subclasses
     *
     * @param Mage_DesignEditor_Model_Change_Collection $collection
     * @throws Magento_Exception
     * @return Mage_DesignEditor_Model_History_CompactInterface
     */
    public function compact($collection = null)
    {
        if (null === $collection) {
            if (!$this->getChangesCollection()) {
                throw new Magento_Exception('Compact collection is missed');
            }
        }
        return $this->setChangesCollection($collection);
    }
}
