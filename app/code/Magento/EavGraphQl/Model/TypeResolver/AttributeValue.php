<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\TypeResolver;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class AttributeValue implements TypeResolverInterface
{
    private const TYPE = 'AttributeValue';

    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @var array
     */
    private array $frontendInputs;

    /**
     * @param AttributeRepository $attributeRepository
     * @param array $frontendInputs
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        array $frontendInputs = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->frontendInputs = $frontendInputs;
    }

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        /** @var Attribute $attr */
        $attr = $this->attributeRepository->get(
            $data['entity_type'],
            $data['code'],
        );

        if (in_array($attr->getFrontendInput(), $this->frontendInputs)) {
            return 'AttributeSelectedOptions';
        }

        return self::TYPE;
    }
}
