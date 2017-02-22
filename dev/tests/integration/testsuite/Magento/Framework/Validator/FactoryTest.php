<?php
/**
 * Integration test for \Magento\Framework\Validator\Factory
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creation of validator config
     *
     * @magentoAppIsolation enabled
     */
    public function testGetValidatorConfig()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Validator\Factory $factory */
        $factory = $objectManager->get('Magento\Framework\Validator\Factory');
        $this->assertInstanceOf('Magento\Framework\Validator\Config', $factory->getValidatorConfig());
        // Check that default translator was set
        $translator = \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator();
        $this->assertInstanceOf('Magento\Framework\Translate\AdapterInterface', $translator);
        $this->assertEquals('Message', new \Magento\Framework\Phrase('Message'));
        $this->assertEquals('Message', $translator->translate('Message'));
        $this->assertEquals(
            'Message with "placeholder one" and "placeholder two"',
            (string)new \Magento\Framework\Phrase('Message with "%1" and "%2"', ['placeholder one', 'placeholder two'])
        );
    }
}
