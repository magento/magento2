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
namespace Magento\Catalog\Service\V1\Product\Attribute;

use Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory;
use Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\TypeBuilder;
use Magento\Catalog\Service\V1\MetadataServiceInterface;
use Magento\Catalog\Service\V1\Product\MetadataServiceInterface as ProductMetadataServiceInterface;

/**
 * Class ReadService
 *
 * @package Magento\Catalog\Service\V1\Product\Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadService implements ReadServiceInterface
{
    /**
     * @var MetadataServiceInterface
     */
    private $metadataService;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory
     */
    private $inputTypeFactory;

    /**
     * @var TypeBuilder
     */
    private $attributeTypeBuilder;

    /**
     * @param MetadataServiceInterface $metadataService
     * @param InputtypeFactory $inputTypeFactory
     * @param TypeBuilder $attributeTypeBuilder
     */
    public function __construct(
        MetadataServiceInterface $metadataService,
        InputtypeFactory $inputTypeFactory,
        TypeBuilder $attributeTypeBuilder
    ) {
        $this->metadataService = $metadataService;
        $this->inputTypeFactory = $inputTypeFactory;
        $this->attributeTypeBuilder = $attributeTypeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function types()
    {
        $types = [];
        $inputType = $this->inputTypeFactory->create();

        foreach ($inputType->toOptionArray() as $option) {
            $types[] = $this->attributeTypeBuilder->populateWithArray($option)->create();
        }
        return $types;
    }

    /**
     * {@inheritdoc}
     */
    public function info($id)
    {
        return $this->metadataService->getAttributeMetadata(
            ProductMetadataServiceInterface::ENTITY_TYPE,
            $id
        );
    }

    /**
     * {@inheritdoc}
     */
    public function search(\Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria)
    {
        return $this->metadataService->getAllAttributeMetadata(
            ProductMetadataServiceInterface::ENTITY_TYPE,
            $searchCriteria
        );
    }
}
