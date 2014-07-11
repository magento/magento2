<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\I18n\Code\Dictionary;

use Magento\Tools\I18n\Code\Dictionary\Phrase;

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
        $reflectionClass = new \ReflectionClass('Magento\Tools\I18n\Code\Dictionary\Phrase');
        $phrase = $reflectionClass->newInstanceArgs($constructArguments);
        $this->assertEquals($result, $phrase->{$getter}());
    }

    /**
     * @return array
     */
    public function dataProviderPhraseCreation()
    {
        return array(
            array(array('phrase', 'translation'), 'getPhrase', 'phrase'),
            array(array('phrase', 'translation'), 'getTranslation', 'translation'),
            array(array('phrase', 'translation', 'context_type'), 'getContextType', 'context_type'),
            array(
                array('phrase', 'translation', 'context_type', 'context_value'),
                'getContextValue',
                array('context_value')
            ),
            array(
                array('phrase', 'translation', 'context_type', array('context_value1', 'context_value2')),
                'getContextValue',
                array('context_value1', 'context_value2')
            ),
            array(
                array('phrase', 'translation', 'context_type', 'context_value1,context_value2'),
                'getContextValue',
                array('context_value1', 'context_value2')
            )
        );
    }

    /**
     * @param array $constructArguments
     * @param string $message
     * @dataProvider dataProviderWrongParametersWhilePhraseCreation
     */
    public function testWrongParametersWhilePhraseCreation($constructArguments, $message)
    {
        $this->setExpectedException('DomainException', $message);

        $reflectionClass = new \ReflectionClass('Magento\Tools\I18n\Code\Dictionary\Phrase');
        $reflectionClass->newInstanceArgs($constructArguments);
    }

    /**
     * @return array
     */
    public function dataProviderWrongParametersWhilePhraseCreation()
    {
        return array(
            array(array(null, 'translation'), 'Missed phrase'),
            array(array('phrase', null), 'Missed translation'),
            array(array('phrase', 'translation', null, new \stdClass()), 'Wrong context type')
        );
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
        return array(
            array('value1', 'setPhrase', 'getPhrase'),
            array('value1', 'setTranslation', 'getTranslation'),
            array('value1', 'setContextType', 'getContextType'),
            array(array('value1'), 'setContextValue', 'getContextValue')
        );
    }

    public function testAddContextValue()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue('context_value2');
        $phrase->addContextValue('context_value3');

        $this->assertEquals(array('context_value1', 'context_value2', 'context_value3'), $phrase->getContextValue());
    }

    public function testContextValueDuplicationResolving()
    {
        $phrase = new Phrase('phrase', 'translation', 'context_type', 'context_value1');
        $phrase->addContextValue('context_value1');
        $phrase->addContextValue('context_value1');

        $this->assertEquals(array('context_value1'), $phrase->getContextValue());
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

        $this->assertEquals(array(), $phrase->getContextValue());
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
