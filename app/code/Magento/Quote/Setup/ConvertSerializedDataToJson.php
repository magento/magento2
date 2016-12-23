<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

/**
 * Convert serialized data in quote tables to JSON
 */
class ConvertSerializedDataToJson
{
    /**
     * @var \Magento\Quote\Setup\QuoteSetup
     */
    private $quoteSetup;

    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var \Magento\Framework\DB\Select\QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var \Magento\Framework\DB\Query\Generator
     */
    private $queryGenerator;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Setup\QuoteSetup $quoteSetup
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     * @param \Magento\Framework\DB\Query\Generator $queryGenerator
     */
    public function __construct(
        \Magento\Quote\Setup\QuoteSetup $quoteSetup,
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory,
        \Magento\Framework\DB\Query\Generator $queryGenerator
    ) {
        $this->quoteSetup = $quoteSetup;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
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
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class
        );
        $fieldDataConverter->convert(
            $this->quoteSetup->getConnection(),
            $this->quoteSetup->getTable('quote_payment'),
            'payment_id',
            'additional_information'
        );
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'code' => [
                        'parameters',
                        'info_buyRequest',
                        'bundle_option_ids',
                        'bundle_selection_attributes',
                    ]
                ]
            ]
        );
        $fieldDataConverter->convert(
            $this->quoteSetup->getConnection(),
            $this->quoteSetup->getTable('quote_item_option'),
            'option_id',
            'value',
            $queryModifier
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
            $fieldDataConverter->convert(
                $this->quoteSetup->getConnection(),
                $this->quoteSetup->getTable('quote_item_option'),
                'option_id',
                'value',
                $queryModifier
            );
        }
    }
}
