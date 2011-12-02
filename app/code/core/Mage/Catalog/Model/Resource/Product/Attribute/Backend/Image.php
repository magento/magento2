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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product image attribute backend
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Attribute_Backend_Image
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * After save
     *
     * @param Varien_Object $object
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Backend_Image
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());

        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()
                ->saveAttribute($object, $this->getAttribute()->getName());
            return;
        }

        try {
            $uploader = new Mage_Core_Model_File_Uploader($this->getAttribute()->getName());
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
        } catch (Exception $e){
            return $this;
        }
        $uploader->save(Mage::getBaseDir('media') . '/catalog/product');

        $fileName = $uploader->getUploadedFileName();
        if ($fileName) {
            $object->setData($this->getAttribute()->getName(), $fileName);
            $this->getAttribute()->getEntity()
                 ->saveAttribute($object, $this->getAttribute()->getName());
        }
        return $this;
    }
}
