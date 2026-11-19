<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Test\Unit\Model\Cache;

use Magento\EavGraphQl\Model\Cache\AttributesFormIdentity;
use Magento\Framework\Api\AttributeInterface;
use PHPUnit\Framework\TestCase;

class AttributesFormIdentityTest extends TestCase
{
    public function testReturnsFormAndAttributeTags(): void
    {
        $attribute = new class implements AttributeInterface {
            public function getAttributeCode()
            {
                return 'firstname';
            }

            public function setAttributeCode($attributeCode)
            {
                return $this;
            }

            public function getValue()
            {
                return null;
            }

            public function setValue($value)
            {
                return $this;
            }

            public function getAttributeId(): string
            {
                return '15';
            }
        };

        $identities = (new AttributesFormIdentity())->getIdentities([
            'formCode' => 'customer_account_create',
            'items' => [
                ['attribute' => $attribute],
                ['attribute' => 'not an attribute'],
            ],
        ]);

        self::assertSame(['EAV_FORM_customer_account_create_FORM', 'EAV_ATTRIBUTE_15'], $identities);
    }

    public function testReturnsNothingWithoutItems(): void
    {
        self::assertSame([], (new AttributesFormIdentity())->getIdentities(['formCode' => 'x', 'items' => []]));
    }
}
