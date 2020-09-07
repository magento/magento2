<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Plugin\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRepository;
use Magento\Swatches\Model\ConvertSwatchAttributeFrontendInput;

/**
 * Plugin for product attribute repository
 */
class RepositoryPlugin
{
    /**
     * @var ConvertSwatchAttributeFrontendInput
     */
    private $convertSwatchAttributeFrontendInput;

    /**
     * @param ConvertSwatchAttributeFrontendInput $convertSwatchAttributeFrontendInput
     */
    public function __construct(
        ConvertSwatchAttributeFrontendInput $convertSwatchAttributeFrontendInput
    ) {
        $this->convertSwatchAttributeFrontendInput = $convertSwatchAttributeFrontendInput;
    }

    /**
     * Performs the conversion of the frontend input value.
     *
     * @param ProductAttributeRepository $subject
     * @param ProductAttributeInterface $attribute
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        ProductAttributeRepository $subject,
        ProductAttributeInterface $attribute
    ): array {
        $data = $attribute->getData();
        $data = $this->convertSwatchAttributeFrontendInput->execute($data);
        $attribute->setData($data);

        return [$attribute];
    }
}
