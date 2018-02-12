<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class ConvertAdditionalDataToJson
 * @package Magento\Swatches\Setup\Patch
 */
class ConvertAdditionalDataToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * ConvertAdditionalDataToJson constructor.
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();
        $this->convertAddDataToJson();
        $this->resourceConnection->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateAdminTextSwatchValues::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.3';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Convert serialized additional data to json.
     */
    private function convertAddDataToJson()
    {
        $fieldConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldConverter->convert(
            $this->resourceConnection->getConnection(),
            $this->resourceConnection->getConnection()->getTableName('catalog_eav_attribute'),
            'attribute_id',
            'additional_data'
        );
    }
}
