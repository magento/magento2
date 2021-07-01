<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Plugin\CatalogSearch\Indexer\FullText\Action;

class DataProvider
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(\Magento\Eav\Model\Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider $subject
     * @param \Magento\Eav\Model\Entity\Attribute[] $result
     * @param string|null $backendType
     * @return \Magento\Eav\Model\Entity\Attribute[]
     */
    public function afterGetSearchableAttributes(
        \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider $subject,
        $result,
        $backendType = null
    ) {
        $isTaxClassIdSearchable = false;

        foreach ($result as $key => $attribute) {
            if ($attribute->getAttributeCode() === 'tax_class_id') {
                $isTaxClassIdSearchable = true;
                break;
            }
        }

        if (!$isTaxClassIdSearchable) {
            $taxClassIdAttribute = $this->eavConfig->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'tax_class_id'
            );

            if (null !== $backendType) {
                if ($taxClassIdAttribute->getBackendType() === $backendType) {
                    $result[$taxClassIdAttribute->getAttributeId()] = $taxClassIdAttribute;
                }
            } else {
                $result[$taxClassIdAttribute->getAttributeId()] = $taxClassIdAttribute;
                $result[$taxClassIdAttribute->getAttributeCode()] = $taxClassIdAttribute;
            }
        }

        return $result;
    }
}
