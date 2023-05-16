<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\TypeResolver;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\EavGraphQl\Model\Uid;
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
     * @var Uid
     */
    private Uid $uid;

    /**
     * @var array
     */
    private array $frontendInputs;

    /**
     * @param AttributeRepository $attributeRepository
     * @param Uid $uid
     * @param array $frontendInputs
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        Uid $uid,
        array $frontendInputs = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->uid = $uid;
        $this->frontendInputs = $frontendInputs;
    }

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        list($entityType, $attributeCode) = $this->uid->decode($data['uid']);

        /** @var Attribute $attr */
        $attr = $this->attributeRepository->get(
            $entityType,
            $attributeCode
        );

        if (in_array($attr->getFrontendInput(), $this->frontendInputs)) {
            return 'AttributeSelectedOptions';
        }

        return self::TYPE;
    }
}
