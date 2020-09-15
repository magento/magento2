<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver;

use Magento\BundleGraphQl\Model\Cart\BundleOptionDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver for bundle product options
 */
class BundleOption implements ResolverInterface
{
    /**
     * @var BundleOptionDataProvider
     */
    private $dataProvider;

    /**
     * @param BundleOptionDataProvider $bundleOptionDataProvider
     */
    public function __construct(
        BundleOptionDataProvider $bundleOptionDataProvider
    ) {
        $this->dataProvider = $bundleOptionDataProvider;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }
        return $this->dataProvider->getData($value['model']);
    }
}
