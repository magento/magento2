<?php
/**
 *
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
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Model\Product\Attribute\InputtypeFactory;
use Magento\Catalog\Model\Product\Attribute\MetadataServiceInterface;
use Magento\Catalog\Model\Product\Attribute\TypeBuilder;

class TypesList implements \Magento\Catalog\Api\ProductAttributeTypesListInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory
     */
    private $inputTypeFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeTypeDataBuilder
     */
    private $attributeTypeBuilder;

    /**
     * @param Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Catalog\Api\Data\ProductAttributeTypeDataBuilder $attributeTypeBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Catalog\Api\Data\ProductAttributeTypeDataBuilder $attributeTypeBuilder
    ) {
        $this->inputTypeFactory = $inputTypeFactory;
        $this->attributeTypeBuilder = $attributeTypeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $types = [];
        $inputType = $this->inputTypeFactory->create();

        foreach ($inputType->toOptionArray() as $option) {
            $types[] = $this->attributeTypeBuilder->populateWithArray($option)->create();
        }
        return $types;
    }
}
