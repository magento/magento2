<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Check that carrier links is valid
 */
class CarrierLinks implements SourceValidatorInterface
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
    public function validate(SourceInterface $source)
    {
        $value = $source->getCarrierLinks();

        $errors = [];
        if (null !== $value) {
            if (!is_array($value)) {
                $errors[] = __('"%1" must be list of SourceCarrierLinkInterface.', SourceInterface::CARRIER_LINKS);
            } else if (count($value) && $source->isUseDefaultCarrierConfig()) {
                $errors[] = __(
                    'You can\'t configure "%1" because you have chosen Global Shipping configuration.',
                    SourceInterface::CARRIER_LINKS
                );
            }
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
