<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\EavGraphQl\Model\GetAttributeSelectedOptionComposite;
use Magento\EavGraphQl\Model\GetAttributeValueInterface;
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
     * @var GetAttributeSelectedOptionComposite
     */
    private GetAttributeSelectedOptionComposite $attributeSelectedOptionComposite;

    /**
     * @var array
     */
    private array $frontendInputs;

    /**
     * @param Uid $uid
     * @param AttributeRepository $attributeRepository
     * @param GetAttributeSelectedOptionComposite $attributeSelectedOptionComposite
     * @param array $frontendInputs
     */
    public function __construct(
        Uid $uid,
        AttributeRepository $attributeRepository,
        GetAttributeSelectedOptionComposite $attributeSelectedOptionComposite,
        array $frontendInputs = []
    ) {
        $this->uid = $uid;
        $this->attributeRepository = $attributeRepository;
        $this->frontendInputs = $frontendInputs;
        $this->attributeSelectedOptionComposite = $attributeSelectedOptionComposite;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $customAttribute): ?array
    {
        $attr = $this->attributeRepository->get(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $customAttribute['attribute_code']
        );

        $result = [
            'uid' => $this->uid->encode(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $customAttribute['attribute_code']
            ),
            'code' => $customAttribute['attribute_code']
        ];

        if (in_array($attr->getFrontendInput(), $this->frontendInputs)) {
            $result['selected_options'] = $this->attributeSelectedOptionComposite->execute($customAttribute);
        } else {
            $result['value'] = $customAttribute['value'];
        }
        return $result;
    }
}
