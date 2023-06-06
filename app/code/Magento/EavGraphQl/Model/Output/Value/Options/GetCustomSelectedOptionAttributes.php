<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value\Options;

use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Custom attribute value provider for customer
 */
class GetCustomSelectedOptionAttributes implements GetAttributeSelectedOptionInterface
{
    /**
     * @var Uid
     */
    private Uid $uid;

    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @param Uid $uid
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        Uid $uid,
        AttributeRepository $attributeRepository,
    ) {
        $this->uid = $uid;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $entity, string $code, string $value): ?array
    {
        $attribute = $this->attributeRepository->get($entity, $code);

        $result = [];
        $selectedValues = explode(',', $value);
        foreach ($attribute->getOptions() as $option) {
            if (!in_array($option->getValue(), $selectedValues)) {
                continue;
            }
            $result[] = [
                'uid' => $this->uid->encode($option->getValue()),
                'value' => $option->getValue(),
                'label' => $option->getLabel()
            ];
        }
        return $result;
    }
}
