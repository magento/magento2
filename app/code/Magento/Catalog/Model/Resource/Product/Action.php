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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product;

/**
 * Catalog Product Mass processing resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Magento\Catalog\Model\Resource\AbstractResource
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->setConnection(
            $resource->getConnection('catalog_read'),
            $resource->getConnection('catalog_write')
        );
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return $this
     * @throws \Exception
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        $object = new \Magento\Framework\Object();
        $object->setIdFieldName('entity_id')->setStoreId($storeId);

        $this->_getWriteAdapter()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {
                $attribute = $this->getAttribute($attrCode);
                if (!$attribute->getAttributeId()) {
                    continue;
                }

                $i = 0;
                foreach ($entityIds as $entityId) {
                    $i++;
                    $object->setId($entityId);
                    // collect data for save
                    $this->_saveAttributeValue($object, $attribute, $value);
                    // save collected data every 1000 rows
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                }
                $this->_processAttributeValues();
            }
            $this->_getWriteAdapter()->commit();
        } catch (\Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        return $this;
    }
}
