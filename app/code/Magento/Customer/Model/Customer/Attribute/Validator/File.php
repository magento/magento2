<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Customer\Attribute\Validator;

use Magento\Customer\Model\Customer\Attribute\ValidatorInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validator for customer custom file attribute.
 */
class File implements ValidatorInterface
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(EavConfig $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public function validate(AttributeInterface $customAttribute): void
    {
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, $customAttribute->getAttributeCode());
        if ($attribute->getFrontendInput() === 'file') {
            if (!preg_match(
                '#^/[a-zA-Z0-9_-]/[a-zA-Z0-9_-]/[a-zA-Z0-9_-]+.[a-z]{3,6}$#',
                $customAttribute->getValue()
            )) {
                throw new LocalizedException(__("The filename has unpermitted symbols."));
            };
        }
    }
}
