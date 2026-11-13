<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\ImportExport\Model\Import;

/**
 * Transform validated rows while retaining the data source's bunch selection.
 */
class ProductBunchTransformer extends Product
{
    /**
     * @inheritdoc
     */
    public function getNextBunch()
    {
        $bunch = parent::getNextBunch();
        if ($bunch === null) {
            return null;
        }
        foreach ($bunch as &$row) {
            $row['name'] = 'Transformed name';
            $row['qty'] = 37;
            if ($this->getBehavior() === Import::BEHAVIOR_DELETE) {
                $row[self::COL_SKU] = 'bunch-target';
            }
        }
        return $bunch;
    }
}
