<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Setup\Patch;

use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class ConvertSerializedDataToJson
 * @package Magento\UrlRewrite\Setup\Patch
 */
class ConvertSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
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
     * ConvertSerializedDataToJson constructor.
     * @param ResourceConnection $resourceConnection
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();
        $this->convertSerializedDataToJson();
        $this->resourceConnection->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Convert native php serialized data to json.
     */
    private function convertSerializedDataToJson()
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $this->resourceConnection->getConnection(),
            $this->resourceConnection->getConnection()->getTableName('url_rewrite'),
            'url_rewrite_id',
            'metadata'
        );
    }
}
