<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product;

use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata;

/**
 * Class AttributeMetadataService
 */
class MetadataService implements MetadataServiceInterface
{
    /** @var  \Magento\Catalog\Service\V1\MetadataService */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Service\V1\Data\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param \Magento\Catalog\Service\V1\MetadataService $metadataService
     * @param \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Service\V1\Data\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Catalog\Service\V1\MetadataService $metadataService,
        \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Service\V1\Data\FilterBuilder $filterBuilder
    ) {
        $this->metadataService = $metadataService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Retrieve custom EAV attribute metadata of product
     *
     * @param int $attributeSetId
     * @return AttributeMetadata[]
     */
    public function getCustomAttributesMetadata($attributeSetId = self::DEFAULT_ATTRIBUTE_SET_ID)
    {
        $customAttributes = [];
        foreach ($this->getProductAttributesMetadata($attributeSetId) as $attributeMetadata) {
            $customAttributes[] = $attributeMetadata;
        }
        return $customAttributes;
    }

    /**
     * Retrieve EAV attribute metadata of product
     *
     * @param int $attributeSetId
     * @return AttributeMetadata[]
     */
    public function getProductAttributesMetadata($attributeSetId = self::DEFAULT_ATTRIBUTE_SET_ID)
    {
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteria */
        $this->searchCriteriaBuilder->addFilter([
            $this->filterBuilder
                ->setField('attribute_set_id')
                ->setValue($attributeSetId)
                ->create()
        ]);

        return $this->metadataService->getAllAttributeMetadata(
            MetadataServiceInterface::ENTITY_TYPE,
            $this->searchCriteriaBuilder->create()
        )->getItems();
    }
}
