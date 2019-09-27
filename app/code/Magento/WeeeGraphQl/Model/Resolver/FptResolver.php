<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Weee\Helper\Data;

class FptResolver implements ResolverInterface
{

    /**
     * @var Data
     */
    private $weeeHelper;

    /**
     * @param Data $weeeHelper
     */
    public function __construct(Data $weeeHelper)
    {
        $this->weeeHelper = $weeeHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $fptArray = [];
        $product = $value['model'];
        $attributes = $this->weeeHelper->getProductWeeeAttributesForDisplay($product);
        foreach ($attributes as $attribute) {
            $fptArray[] = [
                'amount' => [
                    'value' =>  $attribute->getData('amount'),
                    'currency' => $value['final_price']['currency'],
                    ],
                    'label' => $attribute->getData('name')
            ];
        }

        return $fptArray;
    }
}
