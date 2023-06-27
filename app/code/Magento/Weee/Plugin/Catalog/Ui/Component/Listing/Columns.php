<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Catalog\Ui\Component\Listing;

use Magento\Catalog\Ui\Component\Listing\Attribute\Repository;
use Magento\Catalog\Ui\Component\Listing\Columns as DefaultColumns;
use Magento\Weee\Model\Attribute\Backend\Weee\Tax;

/**
 * Class Columns
 */
class Columns
{
    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @param Repository $attributeRepository
     */
    public function __construct(
        Repository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Makes column for FPT attribute in grid not sortable
     *
     * @param DefaultColumns $subject
     */
    public function afterPrepare(DefaultColumns $subject) : void
    {
        foreach ($this->attributeRepository->getList() as $attribute) {
            if ($attribute->getBackendModel() === Tax::class) {
                $column = $subject->getComponent($attribute->getAttributeCode());
                $columnConfig = $column->getData('config');
                $columnConfig['sortable'] = false;
                $column->setData('config', $columnConfig);
            }
        }
    }
}
