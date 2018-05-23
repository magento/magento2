<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Shipping\Model\Config;
use Magento\InventoryApi\Model\SourceValidatorInterface;

/**
 * Check that carrier links is valid
 */
class CarrierLinksValidator implements SourceValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * Shipping config
     *
     * @var Config
     */
    private $shippingConfig;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param Config $shippingConfig
     */
    public function __construct(ValidationResultFactory $validationResultFactory, Config $shippingConfig)
    {
        $this->validationResultFactory = $validationResultFactory;
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @inheritdoc
     */
    public function validate(SourceInterface $source): ValidationResult
    {
        $carrierLinks = $source->getCarrierLinks();
        $errors = [];

        if (null === $carrierLinks) {
            return $this->buildValidationResult($errors);
        }

        if (!is_array($carrierLinks)) {
            $errors[] = __('"%field" must be list of SourceCarrierLinkInterface.', [
                'field' => SourceInterface::CARRIER_LINKS
            ]);
            return $this->buildValidationResult($errors);
        }

        if (count($carrierLinks) && $source->isUseDefaultCarrierConfig()) {
            $errors[] = __('You can\'t configure "%field" because you have chosen Global Shipping configuration.', [
                'field' => SourceInterface::CARRIER_LINKS
            ]);
            return $this->buildValidationResult($errors);
        }

        $availableCarriers = $this->shippingConfig->getAllCarriers();
        foreach ($carrierLinks as $carrierLink) {
            $carrierCode = $carrierLink->getCarrierCode();
            if (array_key_exists($carrierCode, $availableCarriers) === false) {
                $errors[] = __('Carrier with code: "%carrier" don\'t exists.', [
                    'carrier' => $carrierCode
                ]);
            }
        }

        return $this->buildValidationResult($errors);
    }

    /**
     * Build the ValidationResult by given errors.
     *
     * @param array $errors
     * @return ValidationResult
     */
    private function buildValidationResult(array $errors): ValidationResult
    {
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
