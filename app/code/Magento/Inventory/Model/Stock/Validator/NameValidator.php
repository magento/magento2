<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Stock\Validator;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Check that name is valid
 */
class NameValidator implements StockValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(StockInterface $stock)
    {
        $value = (string)$stock->getName();

        if ('' === trim($value)) {
            $errors[] = __('"%field" can not be empty.', ['field' => StockInterface::NAME]);
        } else {
            $errors = [];
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
