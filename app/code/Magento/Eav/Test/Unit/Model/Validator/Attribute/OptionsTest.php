<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Options
 */
namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

use Magento\Eav\Model\Validator\Attribute\Options;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class OptionsTest
 *
 * Validates product attribute options
 */
class OptionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var object
     */
    private $validator;

    /**
     * Set Up
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->validator = $this->objectManager->getObject(
            \Magento\Eav\Model\Validator\Attribute\Options::class
        );
    }

    /**
     * Testing \Magento\Eav\Model\Validator\Attribute\Options::isValid
     *
     * @dataProvider isValidDataProvider
     * @param array $options
     * @param bool $expectedResult
     */
    public function testIsValid(array $options, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, $this->validator->isValid($options));
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function isValidDataProvider(): array
    {
        return [
            [
                [
                    [
                        0 => 'Admin value',
                        1 => 'Default store value'
                    ]
                ],
                true
            ],
            [
                [
                    [
                        0 => 'Admin <strong> value',
                        1 => 'Default store value'
                    ]
                ],
                false
            ],
            [
                [
                    [
                        0 => 'Admin value',
                        1 => 'Default store value</strong>'
                    ]
                ],
                false
            ],
        ];
    }
}
