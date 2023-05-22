<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value;

use Magento\Eav\Model\AttributeRepository;
use Magento\EavGraphQl\Model\Output\Value\Options\GetAttributeSelectedOptionInterface;
use Magento\EavGraphQl\Model\Uid;

/**
 * Custom attribute value provider for customer
 */
class GetCustomAttributes implements GetAttributeValueInterface
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
     * @var GetAttributeSelectedOptionInterface
     */
    private GetAttributeSelectedOptionInterface $getAttributeSelectedOption;

    /**
     * @var array
     */
    private array $frontendInputs;

    /**
     * @param Uid $uid
     * @param AttributeRepository $attributeRepository
     * @param GetAttributeSelectedOptionInterface $getAttributeSelectedOption
     * @param array $frontendInputs
     */
    public function __construct(
        Uid $uid,
        AttributeRepository $attributeRepository,
        GetAttributeSelectedOptionInterface $getAttributeSelectedOption,
        array $frontendInputs = []
    ) {
        $this->uid = $uid;
        $this->attributeRepository = $attributeRepository;
        $this->frontendInputs = $frontendInputs;
        $this->getAttributeSelectedOption = $getAttributeSelectedOption;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $entity, string $code, string $value): ?array
    {
        $attr = $this->attributeRepository->get($entity, $code);

        $result = [
            'uid' => $this->uid->encode($entity, $code),
            'code' => $code
        ];

        if (in_array($attr->getFrontendInput(), $this->frontendInputs)) {
            $result['selected_options'] = $this->getAttributeSelectedOption->execute($entity, $code, $value);
        } else {
            $result['value'] = $value;
        }
        return $result;
    }
}
