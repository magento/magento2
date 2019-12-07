<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\DB\Query\Generator;

/**
 * Convert serialized data in quote tables to JSON
 */
class ConvertSerializedDataToJson
{
    /**
     * @var QuoteSetup
     */
    private $quoteSetup;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * Constructor
     *
     * @param QuoteSetup $quoteSetup
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param QueryModifierFactory $queryModifierFactory
     * @param Generator $queryGenerator
     */
    public function __construct(
        QuoteSetup $quoteSetup,
        AggregatedFieldDataConverter $aggregatedFieldConverter,
        QueryModifierFactory $queryModifierFactory,
        Generator $queryGenerator
    ) {
        $this->quoteSetup = $quoteSetup;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->queryGenerator = $queryGenerator;
    }

    /**
     * Convert data for additional_information field in quote_payment table from serialized
     * to JSON format
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function convert()
    {
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'code' => [
                        'parameters',
                        'info_buyRequest',
                        'attributes',
                        'bundle_option_ids',
                        'bundle_selection_ids',
                        'bundle_selection_attributes',
                    ]
                ]
            ]
        );
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->quoteSetup->getTable('quote_payment'),
                    'payment_id',
                    'additional_information'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->quoteSetup->getTable('quote_payment'),
                    'payment_id',
                    'additional_data'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->quoteSetup->getTable('quote_address'),
                    'address_id',
                    'applied_taxes'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->quoteSetup->getTable('quote_item_option'),
                    'option_id',
                    'value',
                    $queryModifier
                ),
            ],
            $this->quoteSetup->getConnection()
        );

        $select = $this->quoteSetup->getSetup()
            ->getConnection()
            ->select()
            ->from(
                $this->quoteSetup->getSetup()
                    ->getTable('catalog_product_option'),
                ['option_id']
            )
            ->where('type = ?', 'file');
        $iterator = $this->queryGenerator->generate('option_id', $select);
        foreach ($iterator as $selectByRange) {
            $codes = $this->quoteSetup->getSetup()
                ->getConnection()
                ->fetchCol($selectByRange);
            $codes = array_map(
                function ($id) {
                    return 'option_' . $id;
                },
                $codes
            );
            $queryModifier = $this->queryModifierFactory->create(
                'in',
                [
                    'values' => [
                        'code' => $codes
                    ]
                ]
            );
            $this->aggregatedFieldConverter->convert(
                [
                    new FieldToConvert(
                        SerializedToJson::class,
                        $this->quoteSetup->getTable('quote_item_option'),
                        'option_id',
                        'value',
                        $queryModifier
                    ),
                ],
                $this->quoteSetup->getConnection()
            );
        }
    }
}
