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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Visual Design Editor history model
 */
class Mage_DesignEditor_Model_History
{
    /**
     * Base class for all change instances
     */
    const BASE_CHANGE_CLASS = 'Mage_DesignEditor_Model_ChangeAbstract';

    /**
     * Changes collection class
     */
    const CHANGE_COLLECTION_CLASS = 'Mage_DesignEditor_Model_Change_Collection';

    /**
     * Internal collection of changes
     *
     * @var Mage_DesignEditor_Model_Change_Collection
     */
    protected $_collection;

    /**
     * Initialize empty internal collection
     */
    public function __construct()
    {
        $this->_initCollection();
    }

    /**
     * Initialize changes collection
     *
     * @return Mage_DesignEditor_Model_History
     */
    protected function _initCollection()
    {
        $this->_collection = Mage::getModel(self::CHANGE_COLLECTION_CLASS);
        return $this;
    }

    /**
     * Get change instance
     *
     * @param array $data
     * @return Mage_DesignEditor_Model_ChangeAbstract
     */
    protected function _getChangeItem($data)
    {
        return Mage_DesignEditor_Model_Change_Factory::getInstance($data);
    }

    /**
     * Load changes from DB. To be able to effectively compact changes they should be all loaded first.
     *
     * @return Mage_DesignEditor_Model_History
     */
    public function loadChanges()
    {
        return $this;
    }

    /**
     * Add change to internal collection
     *
     * @param Mage_DesignEditor_Model_ChangeAbstract|Varien_Object|array $item
     * @return Mage_DesignEditor_Model_History
     */
    public function addChange($item)
    {
        $baseChangeClass = self::BASE_CHANGE_CLASS;
        if (!$item instanceof $baseChangeClass) {
            $item = $this->_getChangeItem($item);
        }
        $this->_collection->addItem($item);

        return $this;
    }

    /**
     * Add changes to internal collection
     *
     * @param array|Traversable $changes
     * @return Mage_DesignEditor_Model_History
     */
    public function addChanges($changes)
    {
        foreach ($changes as $change) {
            $this->addChange($change);
        }

        return $this;
    }

    /**
     *  Set changes to internal collection
     *
     * @param array|Traversable $changes
     * @return Mage_DesignEditor_Model_History
     */
    public function setChanges($changes)
    {
        $collectionClass = self::CHANGE_COLLECTION_CLASS;
        if ($changes instanceof $collectionClass) {
            $this->_collection = $changes;
        } else {
            $this->_initCollection();
            $this->addChanges($changes);
        }

        return $this;
    }

    /**
     * Get changes collection
     *
     * @return Mage_DesignEditor_Model_Change_Collection
     */
    public function getChanges()
    {
        return $this->_collection;
    }

    /**
     * Render all types of output
     *
     * @param Mage_DesignEditor_Model_History_RendererInterface $renderer
     * @return Mage_DesignEditor_Model_History_RendererInterface
     */
    public function output(Mage_DesignEditor_Model_History_RendererInterface $renderer)
    {
        return $renderer->render($this->_collection);
    }
}
