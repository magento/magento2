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
 * Observer for design editor module
 */
class Mage_DesignEditor_Model_Observer
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_helper;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_DesignEditor_Helper_Data $helper
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_DesignEditor_Helper_Data $helper
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper        = $helper;
    }

    /**
     * Clear temporary layout updates and layout links
     */
    public function clearLayoutUpdates()
    {
        $daysToExpire = $this->_helper->getDaysToExpire();

        // remove expired links
        /** @var $linkCollection Mage_Core_Model_Resource_Layout_Link_Collection */
        $linkCollection = $this->_objectManager->create('Mage_Core_Model_Resource_Layout_Link_Collection');
        $linkCollection->addTemporaryFilter(true)
            ->addUpdatedDaysBeforeFilter($daysToExpire);

        /** @var $layoutLink Mage_Core_Model_Layout_Link */
        foreach ($linkCollection as $layoutLink) {
            $layoutLink->delete();
        }

        // remove expired updates without links
        /** @var $layoutCollection Mage_Core_Model_Resource_Layout_Update_Collection */
        $layoutCollection = $this->_objectManager->create('Mage_Core_Model_Resource_Layout_Update_Collection');
        $layoutCollection->addNoLinksFilter()
            ->addUpdatedDaysBeforeFilter($daysToExpire);

        /** @var $layoutUpdate Mage_Core_Model_Layout_Update */
        foreach ($layoutCollection as $layoutUpdate) {
            $layoutUpdate->delete();
        }
    }
}
