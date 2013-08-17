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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Observer Reindex
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Observer_Reindex
{
    /**
     * Object manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Constructor
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Reindex fulltext
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Catalog_Model_Observer_Reindex
     */
    public function fulltextReindex(Varien_Event_Observer $observer)
    {
        /** @var $category Mage_Catalog_Model_Category */
        $category = $observer->getDataObject();
        if ($category && count($category->getAffectedProductIds()) > 0) {
            /** @var $resource Mage_CatalogSearch_Model_Resource_Fulltext */
            $resource = $this->_objectManager->get('Mage_CatalogSearch_Model_Resource_Fulltext');
            $resource->rebuildIndex(null, $category->getAffectedProductIds());
        }
        return $this;
    }
}
