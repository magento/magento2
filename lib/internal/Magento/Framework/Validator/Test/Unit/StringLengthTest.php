<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Validator\StringLength;
use PHPUnit\Framework\TestCase;

/**
 * Test case for \Magento\Framework\Validator\StringLength
 */
class StringLengthTest extends TestCase
{
    /**
     * @var StringLength
     */
    protected $_validator;

    protected function setUp(): void
    {
        $this->_validator = new StringLength();
    }

    public function testDefaultEncoding()
    {
        $this->assertEquals('UTF-8', $this->_validator->getEncoding());
    }

    /**
     * @dataProvider isValidDataProvider
     * @param string $value
     * @param int $maxLength
     * @param bool $isValid
     */
    public function testIsValid($value, $maxLength, $isValid)
    {
        $this->_validator->setMax($maxLength);
        $this->assertEquals($isValid, $this->_validator->isValid($value));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            ['строка', 6, true],
            ['строка', 5, false],
            ['string', 6, true],
            ['string', 5, false]
        ];
    }
}
