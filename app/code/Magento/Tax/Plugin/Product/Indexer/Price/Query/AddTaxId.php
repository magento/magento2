<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Plugin\Product\Indexer\Price\Query;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Framework\DB\Select;

/**
 * Add tax_class_id column to price index select
 */
class AddTaxId
{
    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * @param JoinAttributeProcessor $joinAttributeProcessor
     */
    public function __construct(JoinAttributeProcessor $joinAttributeProcessor)
    {
        $this->joinAttributeProcessor = $joinAttributeProcessor;
    }

    /**
     * @param BaseFinalPrice $subject
     * @param Select $select
     * @param int $websiteId
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuery(BaseFinalPrice $subject, Select $select, int $websiteId): Select
    {
        $taxClassId = $this->joinAttributeProcessor->process($select, $websiteId, 'tax_class_id');
        $select->columns(['tax_class_id' => $taxClassId]);

        return $select;
    }
}
