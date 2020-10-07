<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageSpecificAttributeGraphQl\Model\Resolver;

use Magento\Customer\Api\MetadataInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\PageSpecificAttributeGraphQl\Model\Formatter\AttributesMetadata;

/**
 * Page specific custom attributes resolver
 */
class PageSpecificCustomAttributes implements ResolverInterface
{
    /**
     * @var array
     */
    private $allowedPageTypes;

    /**
     * @var AttributesMetadata
     */
    private $formatter;

    /**
     * @param array $allowedPageTypes
     * @param AttributesMetadata $formatter
     */
    public function __construct(
        array $allowedPageTypes,
        AttributesMetadata $formatter
    ) {
        $this->allowedPageTypes = $allowedPageTypes;
        $this->formatter = $formatter;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['page_type'])) {
            throw new GraphQlInputException(__('Please enter page_type.'));
        }

        $pageType = strtolower($args['page_type']);

        $attributesMetadata = [];

        if (isset($this->allowedPageTypes[$pageType])) {
            /** @var $model MetadataInterface */
            $model = $this->allowedPageTypes[$pageType]['model'];

            if (isset($this->allowedPageTypes[$pageType]['form'])) {
                $attributesMetadata = $model->getAttributes($this->allowedPageTypes[$pageType]['form']);
            } else {
                $attributesMetadata = $model->getAllAttributesMetadata();
            }
        }

        $attributes = [];

        foreach ($attributesMetadata as $attribute) {
            $attributes[] = $this->formatter->format($attribute);
        }

        return ['items' => $attributes];
    }
}
