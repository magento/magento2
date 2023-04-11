<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\MetadataInterface;
use Magento\EavGraphQl\Model\GetAttributesFormInterface;
use Magento\EavGraphQl\Model\Uid;

/**
 * Attributes form provider for customer
 */
class GetAttributesForm implements GetAttributesFormInterface
{
    /**
     * @var MetadataInterface
     */
    private MetadataInterface $entity;

    /**
     * @var Uid
     */
    private Uid $uid;

    /**
     * @var string
     */
    private string $type;

    /**
     * @param MetadataInterface $metadata
     * @param Uid $uid
     * @param string $type
     */
    public function __construct(MetadataInterface $metadata, Uid $uid, string $type)
    {
        $this->entity = $metadata;
        $this->uid = $uid;
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $formCode): ?array
    {
        $attributes = [];
        foreach ($this->entity->getAttributes($formCode) as $attribute) {
            $attributes[] = $this->uid->encode($this->type, $attribute->getAttributeCode());
        }
        return $attributes;
    }
}
