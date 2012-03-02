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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Catalog_Model_Convert_Adapter_Catalog
    extends Mage_Dataflow_Model_Convert_Adapter_Abstract
{
    public function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Convert');
        }
        return $this->_resource;
    }

    public function load()
    {
        $res = $this->getResource();

        $this->setData(array(
            'Products' => $res->exportProducts(),
            'Categories' => $res->exportCategories(),
            'Image Gallery' => $res->exportImageGallery(),
            'Product Links' => $res->exportProductLinks(),
            'Products in Categories' => $res->exportProductsInCategories(),
            'Products in Stores' => $res->exportProductsInStores(),
            'Attributes' => $res->exportAttributes(),
            'Attribute Sets' => $res->exportAttributeSets(),
            'Attribute Options' => $res->exportAttributeOptions(),
        ));

        return $this;
    }

    public function save()
    {
        /*
        $res = $this->getResource();

        foreach (array('Attributes', 'Attribute Sets', 'Attribute Options', 'Products', 'Categories', ''))

        $this->setData

        echo "<pre>".print_r($this->getData(),1)."</pre>";

        */
        return $this;
    }
}
