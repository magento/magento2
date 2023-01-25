<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Laminas\Validator\Callback;
use Laminas\Validator\Identical;
use Magento\Framework\Validator\StringLength;
use Magento\Framework\Validator\DataObject;
use PHPUnit\Framework\TestCase;

class ObjectTest extends TestCase
{
    /**
     * @var DataObject
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new DataObject();

        $fieldOneExactValue = new Identical('field_one_value');
        $fieldOneExactValue->setMessage("'field_one' does not match expected value");
        $fieldOneLength = new StringLength(['min' => 10]);

        $fieldTwoExactValue = new Identical('field_two_value');
        $fieldTwoExactValue->setMessage("'field_two' does not match expected value");
        $fieldTwoLength = new StringLength(['min' => 5]);

        $entityValidity = new Callback([$this, 'isEntityValid']);
        $entityValidity->setMessage('Entity is not valid.');

        $this->_model->addRule(
            $fieldOneLength,
            'field_one'
        )->addRule(
            $fieldOneExactValue,
            'field_one'
        )->addRule(
            $fieldTwoLength,
            'field_two'
        )->addRule(
            $fieldTwoExactValue,
            'field_two'
        )->addRule(
            $entityValidity
        );
    }

    protected function tearDown(): void
    {
        $this->_model = null;
    }

    /**
     * Entity validation routine to be used as a callback
     *
     * @param \Magento\Framework\DataObject $entity
     * @return bool
     */
    public function isEntityValid(\Magento\Framework\DataObject $entity)
    {
        return (bool)$entity->getData('is_valid');
    }

    public function testAddRule()
    {
        $actualResult = $this->_model->addRule(new Identical('field_one_value'), 'field_one');
        $this->assertSame($this->_model, $actualResult, 'Methods chaining is broken.');
    }

    public function testGetMessages()
    {
        $messages = $this->_model->getMessages();
        $this->assertIsArray($messages);
    }

    /**
     * @param array $inputEntityData
     * @param array $expectedErrors
     * @dataProvider validateDataProvider
     */
    public function testIsValid(array $inputEntityData, array $expectedErrors)
    {
        $entity = new \Magento\Framework\DataObject($inputEntityData);
        $isValid = $this->_model->isValid($entity);
        $this->assertFalse($isValid, 'Validation is expected to fail.');

        $actualMessages = $this->_model->getMessages();
        $this->assertCount(count($expectedErrors), $actualMessages, 'Number of messages does not meet expectations.');
        foreach ($expectedErrors as $errorIndex => $expectedErrorMessage) {
            /** @var $actualMessage \Magento\Framework\Message\AbstractMessage */
            $actualMessage = $actualMessages[$errorIndex];
            $this->assertEquals($expectedErrorMessage, $actualMessage);
        }
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'only "field_one" is invalid' => [
                ['field_one' => 'one_value', 'field_two' => 'field_two_value', 'is_valid' => true],
                ["'one_value' is less than 10 characters long", "'field_one' does not match expected value"],
            ],
            'only "field_two" is invalid' => [
                ['field_one' => 'field_one_value', 'field_two' => 'two_value', 'is_valid' => true],
                ["'field_two' does not match expected value"],
            ],
            'entity as a whole is invalid' => [
                ['field_one' => 'field_one_value', 'field_two' => 'field_two_value'],
                ['Entity is not valid.'],
            ],
            'errors aggregation' => [
                ['field_one' => 'one_value', 'field_two' => 'two'],
                [
                    "'one_value' is less than 10 characters long",
                    "'field_one' does not match expected value",
                    "'two' is less than 5 characters long",
                    "'field_two' does not match expected value",
                    'Entity is not valid.'
                ],
            ]
        ];
    }
}
