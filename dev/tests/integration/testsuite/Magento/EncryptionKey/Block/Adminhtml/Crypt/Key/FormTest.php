<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\EncryptionKey\Block\Adminhtml\Crypt\Key;

/**
 * Test class for \Magento\EncryptionKey\Block\Adminhtml\Crypt\Key\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $objectManager->get('Magento\Framework\View\DesignInterface')
            ->setArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            ->setDefaultDesignTheme();

        $block = $objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock('Magento\EncryptionKey\Block\Adminhtml\Crypt\Key\Form');

        $prepareFormMethod = new \ReflectionMethod(
            'Magento\EncryptionKey\Block\Adminhtml\Crypt\Key\Form',
            '_prepareForm'
        );
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();

        $this->assertEquals('edit_form', $form->getId());
        $this->assertEquals('post', $form->getMethod());

        foreach (['enc_key_note', 'generate_random', 'crypt_key', 'main_fieldset'] as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
        }

        $generateRandomField = $form->getElement('generate_random');
        $this->assertEquals('select', $generateRandomField->getType());
        $this->assertEquals([ 0 => 'No', 1 => 'Yes'], $generateRandomField->getOptions());

        $cryptKeyField = $form->getElement('crypt_key');
        $this->assertEquals('text', $cryptKeyField->getType());
        $this->assertEquals('crypt_key', $cryptKeyField->getName());
    }
}
