<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

/**
 * Fetch product attribute option data including attribute info
 * Return data in format:
 * [
 *  attribute_code => [
 *      attribute_code => code,
 *      attribute_label => attribute label,
 *      option_label => option label,
 *      options => [option_id => 'option label', ...],
 *  ]
 * ...
 * ]
 */
class AttributeOptionProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    private $productFactory;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->productFactory = $productFactory;
    }

    /**
     * Get option data. Return list of attributes with option data
     *
     * @param array $optionIds
     * @param int|null $storeId
     * @param array $attributeCodes
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getOptions(array $optionIds, array $attributeCodes = [], $storeId): array
    {
        if (!$optionIds) {
            return [];
        }

        $storeId = $storeId ?: Store::DEFAULT_STORE_ID;
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()            
            ->from(
                ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                [
                    'attribute_id' => 'a.attribute_id',
                    'attribute_code' => 'a.attribute_code',
                    'attribute_label' => 'a.frontend_label',
                ]
            )
            ->joinLeft(
                ['options' => $this->resourceConnection->getTableName('eav_attribute_option')],
                'a.attribute_id = options.attribute_id',
                []
            )
            ->joinLeft(
                ['attrlabel' => $this->resourceConnection->getTableName('eav_attribute_label')],
                'a.attribute_id = attrlabel.attribute_id',
                [
                    'store_label' => 'attrlabel.value',
                    'attribute_store_id' => 'attrlabel.store_id'
                ]
            )
            ->joinLeft(
                ['option_value' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                'options.option_id = option_value.option_id',
                [
                    'option_id' => 'option_value.option_id',
                    'option_store_id' => 'option_value.store_id'
                ]
            )->joinLeft(
                ['option_value_store' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                "options.option_id = option_value_store.option_id AND option_value_store.store_id = {$storeId}",
                [
                    'option_label' => $connection->getCheckSql(
                        'option_value_store.value_id > 0',
                        'option_value_store.value',
                        'option_value.value'
                    )
                ]
            )->where(
                'a.attribute_id = options.attribute_id AND option_value.store_id = ?',
                Store::DEFAULT_STORE_ID
            );

        $select->where('option_value.option_id IN (?)', $optionIds);
       
 
        if (!empty($attributeCodes)) {
            $select->orWhere(
                'a.attribute_code in (?) AND a.frontend_input = \'boolean\'',
                $attributeCodes
            );
        }
            
        return $this->formatResult($select, $storeId);
    }

    /**
     * Format result
     *
     * @param \Magento\Framework\DB\Select $select
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function formatResult(\Magento\Framework\DB\Select $select, $storeId): array
    {        
        $statement = $this->resourceConnection->getConnection()->query($select);

        $result = [];
        $poductReource=$this->productFactory->create();
        
        while ($option = $statement->fetch()) {
                
            if($poductReource->getAttribute($option['attribute_code'])->getStoreLabel($storeId)){
                $attributeLabel = $poductReource->getAttribute($option['attribute_code'])->getStoreLabel($storeId);    
            }else{
                $attributeLabel = $option['attribute_label'];
            }
            
            $attribute = $poductReource->getAttribute($option['attribute_code']);
            if ($attribute->usesSource()) {
              $option_Text = $attribute->getSource()->getOptionText($option['option_id']);
            }            
          
            if (!isset($result[$option['attribute_code']])) {              
                $result[$option['attribute_code']] = [
                    'attribute_id' => $option['attribute_id'],
                    'attribute_code' => $option['attribute_code'],
                    'attribute_label' =>  $attributeLabel,
                    'options' => [],
                ];
            }            
                
            $result[$option['attribute_code']]['options'][$option['option_id']] = $option_Text;
        }
      
        return $result;
    }
}
