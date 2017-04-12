<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Option;

use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Option\Validator
     */
    private $validator;

    /**
     * SetUp method for unit test
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $validate = $helper->getObject(\Magento\Framework\Validator\NotEmpty::class, ['options' => NotEmpty::ALL]);

        $validateFactory = $this->getMockBuilder(\Magento\Framework\Validator\NotEmptyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $validateFactory->expects($this->once())
            ->method('create')
            ->willReturn($validate);

        $this->validator = $helper->getObject(
            \Magento\Bundle\Model\Option\Validator::class,
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Option $option */
        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
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
            ['title', null, false, ['type' => 'type is a required field.']],
            [null, 'select', false, ['title' => 'title is a required field.']],
            [null, null, false, ['type' => 'type is a required field.', 'title' => 'title is a required field.']]
        ];
    }
}
