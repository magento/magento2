<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
     * @var Uid
     */
    private $idEncoder;

    /**
     * @param Uid $idEncoder
     * @param array $allowedPageTypes
     */
    public function __construct(
        Uid $idEncoder,
        array $allowedPageTypes = []
    ) {
        $this->idEncoder = $idEncoder;
        $this->allowedPageTypes = $allowedPageTypes;
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
            $model = $this->allowedPageTypes[$pageType]['model'];

            if (isset($this->allowedPageTypes[$pageType]['form'])) {
                $attributesMetadata = $model->getAttributes($this->allowedPageTypes[$pageType]['form']);
            } else {
                $attributesMetadata = $model->getAllAttributesMetadata();
            }
        }

        $attributes = [];

        foreach ($attributesMetadata as $attribute) {
            $attributes[] = [
                'uid' => $this->idEncoder->encode($attribute->getAttributeId()),
                'attribute_code' => $attribute->getAttributeCode(),
                'entity_type' => $attribute->getEntityTypeId(),
                'attribute_type' => ucfirst($attribute->getBackendType()),
                'input_type' => $attribute->getFrontendInput()
            ];
        }

        return ['items' => $attributes];
    }
}
