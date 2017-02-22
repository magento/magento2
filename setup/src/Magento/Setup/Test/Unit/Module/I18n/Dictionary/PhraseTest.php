<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary;

use \Magento\Setup\Module\I18n\Dictionary\Phrase;

class PhraseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $constructArguments
     * @param string $getter
     * @param string|array $result
     * @dataProvider dataProviderPhraseCreation
     */
    public function testPhraseCreation($constructArguments, $getter, $result)
    {
        $reflectionClass = new \ReflectionClass('Magento\Setup\Module\I18n\Dictionary\Phrase');
        $phrase = $reflectionClass->newInstanceArgs($constructArguments);
        $this->assertEquals($result, $phrase->{$getter}());
    }

    /**
     * @return array
     */
    public function dataProviderPhraseCreation()
    {
        return [
            [['phrase', 'translation'], 'getPhrase', 'phrase'],
            [['phrase', 'translation'], 'getTranslation', 'translation'],
            [['phrase', 'translation', 'context_type'], 'getContextType', 'context_type'],
            [
                ['phrase', 'translation', 'context_type', 'context_value'],
                'getContextValue',
                ['context_value']
            ],
            [
                ['phrase', 'translation', 'context_type', ['context_value1', 'context_value2']],
                'getContextValue',
                ['context_value1', 'context_value2']
            ],
            [
                ['phrase', 'translation', 'context_type', 'context_value1,context_value2'],
                'getContextValue',
                ['context_value1', 'context_value2']
            ]
        ];
    }

    /**
     * @param array $constructArguments
     * @param string $message
     * @dataProvider dataProviderWrongParametersWhilePhraseCreation
     */
    public function testWrongParametersWhilePhraseCreation($constructArguments, $message)
    {
        $this->setExpectedException('DomainException', $message);

        $reflectionClass = new \ReflectionClass('Magento\Setup\Module\I18n\Dictionary\Phrase');
        $reflectionClass->newInstanceArgs($constructArguments);
    }

    /**
     * @return array
     */
    public function dataProviderWrongParametersWhilePhraseCreation()
    {
        return [
            [[null, 'translation'], 'Missed phrase'],
            [['phrase', null], 'Missed translation'],
            [['phrase', 'translation', null, new \stdClass()], 'Wrong context type']
        ];
    }

    /**
     * @param string $value
     * @param string $setter
     * @param string $getter
     * @dataProvider dataProviderAccessorMethods
     */
    public function testAccessorMethods($value, $setter, $getter)
    {
        $phrase = new Phrase('phrase', 'translation');
        $phrase->{$setter}($value);

        $this->assertEquals($value, $phrase->{$getter}());
    }

    /**
     * @return array
     */
    public function dataProviderAccessorMethods()
    {
        return [
            ['value1', 'setPhrase', 'getPhrase'],
            ['value1', 'setTranslation', 'getTranslation'],
            ['value1', 'setContextType', 'getContextType'],
            [['value1'], 'setContextValue', 'getContextValue']
        ];
    }

    public function testAddContextValue()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue('context_value2');
        $phrase->addContextValue('context_value3');

        $this->assertEquals(['context_value1', 'context_value2', 'context_value3'], $phrase->getContextValue());
    }

    public function testContextValueDuplicationResolving()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue('context_value1');
        $phrase->addContextValue('context_value1');

        $this->assertEquals(['context_value1'], $phrase->getContextValue());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Context value is empty
     */
    public function testAddEmptyContextValue()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue(null);
    }

    public function testContextValueReset()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue('context_value2');
        $phrase->setContextValue(null);

        $this->assertEquals([], $phrase->getContextValue());
    }

    public function testGetContextValueAsString()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue('context_value2');
        $phrase->addContextValue('context_value3');

        $this->assertEquals('context_value1,context_value2,context_value3', $phrase->getContextValueAsString());
    }

    public function testGetContextValueAsStringWithCustomSeparator()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue('context_value2');
        $phrase->addContextValue('context_value3');

        $this->assertEquals('context_value1::context_value2::context_value3', $phrase->getContextValueAsString('::'));
    }

    public function testGetKey()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $this->assertEquals('phrase::context_type', $phrase->getKey());
    }
}
