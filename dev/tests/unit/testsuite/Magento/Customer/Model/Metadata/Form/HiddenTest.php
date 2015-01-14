<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

class HiddenTest extends TextTest
{
    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @return Hidden
     */
    protected function getClass($value)
    {
        return new Hidden(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0,
            false,
            $this->stringHelper
        );
    }
}
