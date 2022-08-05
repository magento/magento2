<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Option;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Option\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\NotEmptyFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * SetUp method for unit test
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $validate = $helper->getObject(NotEmpty::class, ['options' => NotEmpty::ALL]);

        $validateFactory = $this->getMockBuilder(NotEmptyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $validateFactory->expects($this->once())
            ->method('create')
            ->willReturn($validate);

        $this->validator = $helper->getObject(
            Validator::class,
            ['notEmptyFactory' => $validateFactory]
        );
    }

    /**
     * Test for method isValid
     *
     * @param string $title
     * @param string $type
     * @param bool $isValid
     * @param string[] $expectedMessages
     * @dataProvider providerIsValid
     */
    public function testIsValid($title, $type, $isValid, $expectedMessages)
    {
        /** @var MockObject|Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->setMethods(['getTitle', 'getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);
        $option->expects($this->once())
            ->method('getType')
            ->willReturn($type);

        $this->assertEquals($isValid, $this->validator->isValid($option));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Provider for testIsValid
     */
    public function providerIsValid()
    {
        return [
            ['title', 'select', true, []],
            ['title', null, false, ['type' => '"type" is required. Enter and try again.']],
            [null, 'select', false, ['title' => '"title" is required. Enter and try again.']],
            [
                null,
                null,
                false,
                [
                    'type' => '"type" is required. Enter and try again.',
                    'title' => '"title" is required. Enter and try again.'
                ]
            ]
        ];
    }
}
