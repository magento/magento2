<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Setup\ConvertSerializedDataToJsonFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class ConvertSerializedDataToJson
 * @package Magento\Quote\Setup\Patch
 */
class ConvertSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var ConvertSerializedDataToJsonFactory
     */
    private $convertSerializedDataToJsonFactory;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QuoteSetupFactory $quoteSetupFactory,
        ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->convertSerializedDataToJsonFactory = $convertSerializedDataToJsonFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $quoteSetup = $this->quoteSetupFactory->create();
        $this->convertSerializedDataToJsonFactory->create(['quoteSetup' => $quoteSetup])->convert();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            InstallEntityTypes::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.6';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
