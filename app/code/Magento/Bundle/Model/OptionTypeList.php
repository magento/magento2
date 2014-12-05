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
namespace Magento\Bundle\Model;

class OptionTypeList implements \Magento\Bundle\Api\ProductOptionTypeListInterface
{
    /**
     * @var Source\Option\Type
     */
    protected $types;

    /**
     * @var \Magento\Bundle\Api\Data\OptionTypeDataBuilder
     */
    protected $typeBuilder;

    /**
     * @param Source\Option\Type $type
     * @param \Magento\Bundle\Api\Data\OptionTypeDataBuilder $typeBuilder
     */
    public function __construct(
        \Magento\Bundle\Model\Source\Option\Type $type,
        \Magento\Bundle\Api\Data\OptionTypeDataBuilder $typeBuilder
    ) {
        $this->types = $type;
        $this->typeBuilder = $typeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $optionList = $this->types->toOptionArray();

        /** @var \Magento\Bundle\Api\Data\OptionTypeInterface[] $typeList */
        $typeList = [];
        foreach ($optionList as $option) {
            $typeList[] = $this->typeBuilder->setCode($option['value'])->setLabel($option['label'])->create();
        }
        return $typeList;
    }
}
