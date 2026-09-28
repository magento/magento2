<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\TypeResolver;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

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
     *
     * @throws GraphQlNoSuchEntityException
     */
    public function resolveType(array $data): string
    {
        try {
            /** @var Attribute $attr */
            $attr = $this->attributeRepository->get(
                $data['entity_type'],
                $data['code'],
            );
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        if (in_array($attr->getFrontendInput(), $this->frontendInputs)) {
            return 'AttributeSelectedOptions';
        }

        return self::TYPE;
    }
}
