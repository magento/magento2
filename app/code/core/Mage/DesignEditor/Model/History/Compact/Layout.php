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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * History compaction strategy to compact layout changes
 */
class Mage_DesignEditor_Model_History_Compact_Layout extends Mage_DesignEditor_Model_History_CompactAbstract
{
    /**
     * Scheduled move actions
     *
     * @var array
     */
    protected $_scheduledMoves = array();

    /**
     * Scheduled remove actions
     *
     * @var array
     */
    protected $_scheduledRemoves = array();

    /**
     * Run compact strategy on given collection
     *
     * @param Mage_DesignEditor_Model_Change_Collection $collection
     * @return Mage_DesignEditor_Model_History_Compact_Layout
     */
    public function compact($collection = null)
    {
        parent::compact($collection)->_scheduleActions()->_compactLayoutChanges();
    }

    /**
     * Schedule layout actions
     *
     * @return Mage_DesignEditor_Model_History_Compact_Layout
     */
    protected function _scheduleActions()
    {
        /** @var $change Mage_DesignEditor_Model_Change_LayoutAbstract */
        foreach ($this->getChangesCollection() as $changeKey => $change) {
            if (!$change instanceof Mage_DesignEditor_Model_Change_LayoutAbstract) {
                continue;
            }
            switch ($change->getData('action_name')) {
                case Mage_DesignEditor_Model_Change_Layout_Remove::LAYOUT_DIRECTIVE_REMOVE:
                    $this->_scheduledRemoves[$change->getData('element_name')][] = array(
                        'collection_key' => $changeKey
                    );
                    break;

                case Mage_DesignEditor_Model_Change_Layout_Move::LAYOUT_DIRECTIVE_MOVE:
                    $this->_scheduledMoves[$change->getData('element_name')][] = array(
                        'collection_key' => $changeKey,
                        'change' => $change
                    );
                    break;

                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * Compact layout changes
     *
     * @return Mage_DesignEditor_Model_History_Compact_Layout
     */
    protected function _compactLayoutChanges()
    {
        return $this->_compactRemoves()->_compactMoves();
    }

    /**
     * Compact remove layout directives
     *
     * @return Mage_DesignEditor_Model_History_Compact_Layout
     */
    protected function _compactRemoves()
    {
        foreach ($this->_scheduledRemoves as $itemName => $removeItem) {
            $arrayToRemove = array();
            if (!empty($this->_scheduledMoves[$itemName])) {
                $arrayToRemove = $this->_scheduledMoves[$itemName];
            }
            $this->_removeElements(array_merge($arrayToRemove, array_slice($removeItem, 0, count($removeItem) - 1)));
        }
        return $this;
    }

    /**
     * Compact move layout directives
     *
     * @return Mage_DesignEditor_Model_History_Compact_Layout
     */
    protected function _compactMoves()
    {
        foreach ($this->_scheduledMoves as $moveItem) {
            if (count($moveItem) === 1) {
                continue;
            }
            $arrayToRemove = array();
            $lastMove = array_pop($moveItem);
            $lastMoveElement = $lastMove['change'];

            $firstMove = $moveItem[0]['change'];
            $originContainer = $firstMove->getData('origin_container');
            $originOrder = $firstMove->getData('origin_order');
            $hasContainerChanged = $lastMoveElement->getData('destination_container') != $originContainer;
            if (!$hasContainerChanged) {
                $hasOrderChanged = $lastMoveElement->getData('destination_order') != $originOrder;
                if (!$hasOrderChanged) {
                    $arrayToRemove = array($lastMove);
                }
            }

            $this->_removeElements(array_merge($arrayToRemove, $moveItem));
        }
        return $this;
    }

    /**
     * Remove array of elements from change collection
     *
     * @param array $elements
     * @return Mage_DesignEditor_Model_History_Compact_Layout
     */
    protected function _removeElements($elements)
    {
        foreach ($elements as $element) {
            $this->getChangesCollection()->removeItemByKey($element['collection_key']);
        }
        return $this;
    }
}
