<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Entity;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Eav\Model\Entity\GetCustomAttributeCodesInterface;
use Magento\Framework\Api\MetadataServiceInterface;

class GetCategoryCustomAttributeCodes implements GetCustomAttributeCodesInterface
{
    /**
     * @var GetCustomAttributeCodesInterface
     */
    private $baseCustomAttributeCodes;

    /**
     * @param GetCustomAttributeCodesInterface $baseCustomAttributeCodes
     */
    public function __construct(
        GetCustomAttributeCodesInterface $baseCustomAttributeCodes
    ) {
        $this->baseCustomAttributeCodes = $baseCustomAttributeCodes;
    }

    /**
     * @inheritdoc
     */
    public function execute(MetadataServiceInterface $metadataService): array
    {
        $customAttributesCodes = $this->baseCustomAttributeCodes->execute($metadataService);
        return array_diff($customAttributesCodes, CategoryInterface::ATTRIBUTES);
    }
}
