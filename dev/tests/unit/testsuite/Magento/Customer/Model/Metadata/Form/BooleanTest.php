<?php
/**
 * test Magento\Customer\Model\Metadata\Form\Boolean
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

class BooleanTest extends AbstractFormTestCase
{
    /**
     * @param mixed $value to assign to boolean
     * @param mixed $expected text output
     * @dataProvider getOptionTextDataProvider
     */
    public function testGetOptionText($value, $expected)
    {
        // calling outputValue() will cause the protected method getOptionText() to be called
        $boolean = new Boolean(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0
        );
        $this->assertSame($expected, $boolean->outputValue());
    }

    public function getOptionTextDataProvider()
    {
        return [
            '0' => ['0', 'No'],
            '1' => ['1', 'Yes'],
            'int 5' => [5, ''],
            'Null' => [null, ''],
            'Invalid' => ['Invalid', ''],
            'Empty string' => ['', '']
        ];
    }
}
