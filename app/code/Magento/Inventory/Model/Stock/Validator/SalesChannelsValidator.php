<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Stock\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Model\StockValidatorInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Check that sales channels are correct
 */
class SalesChannelsValidator implements StockValidatorInterface
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
    public function validate(StockInterface $stock): ValidationResult
    {
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();

        $errors = [];
        if (is_array($salesChannels)) {
            foreach ($salesChannels as $salesChannel) {
                $type = (string)$salesChannel->getType();
                if ('' === trim($type)) {
                    $errors[] = __('"%field" can not be empty.', ['field' => SalesChannelInterface::TYPE]);
                }

                $code = (string)$salesChannel->getCode();
                if ('' === trim($code)) {
                    $errors[] = __('"%field" can not be empty.', ['field' => SalesChannelInterface::CODE]);
                }
            }
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
