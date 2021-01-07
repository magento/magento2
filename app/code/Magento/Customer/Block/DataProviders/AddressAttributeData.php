<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\DataProviders;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Provides address attribute data into template.
 */
class AddressAttributeData implements ArgumentInterface
{
    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param AddressMetadataInterface $addressMetadata
     * @param Escaper $escaper
     */
    public function __construct(
        AddressMetadataInterface $addressMetadata,
        Escaper $escaper
    ) {

        $this->addressMetadata = $addressMetadata;
        $this->escaper = $escaper;
    }

    /**
     * Returns frontend label for attribute.
     *
     * @param string $attributeCode
     * @return string
     * @throws LocalizedException
     */
    public function getFrontendLabel(string $attributeCode): string
    {
        try {
            $attribute =  $this->addressMetadata->getAttributeMetadata($attributeCode);
            $frontendLabel = $attribute->getStoreLabel() ?: $attribute->getFrontendLabel();
        } catch (NoSuchEntityException $e) {
            $frontendLabel = '';
        }

        return $this->escaper->escapeHtml(__($frontendLabel));
    }
}
