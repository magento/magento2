<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Catalog\Model\Product\Type;

use Magento\Catalog\Model\Product\Type\AbstractType as Subject;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Bundle\Model\Product\SingleChoiceProvider;

/**
 * Plugin to add possibility to add bundle product with single option from list
 */
class AbstractType
{
    /**
     * @var SingleChoiceProvider
     */
    private $singleChoiceProvider;

    /**
     * @param SingleChoiceProvider $singleChoiceProvider
     */
    public function __construct(
        SingleChoiceProvider $singleChoiceProvider
    ) {
        $this->singleChoiceProvider = $singleChoiceProvider;
    }

    /**
     * Add possibility to add to cart from the list in case of one required option
     *
     * @param Subject $subject
     * @param bool $result
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsPossibleBuyFromList(Subject $subject, $result, $product)
    {
        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
            $isSingleChoice = $this->singleChoiceProvider->isSingleChoiceAvailable($product);
            if ($isSingleChoice === true) {
                $result = $isSingleChoice;
            }
        }
        return $result;
    }
}
