<?php
/**
 * Integration test for \Magento\Core\Model\Validator\Factory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Validator;

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
        /** @var \Magento\Core\Model\Validator\Factory $factory */
        $factory = $objectManager->get('Magento\Core\Model\Validator\Factory');
        $this->assertInstanceOf('Magento\Framework\Validator\Config', $factory->getValidatorConfig());
        // Check that default translator was set
        $translator = \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator();
        $this->assertInstanceOf('Magento\Framework\Translate\AdapterInterface', $translator);
        $this->assertEquals('Message', __('Message'));
        $this->assertEquals('Message', $translator->translate('Message'));
        $this->assertEquals(
            'Message with "placeholder one" and "placeholder two"',
            (string)__('Message with "%1" and "%2"', 'placeholder one', 'placeholder two')
        );
    }
}
