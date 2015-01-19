<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Customer\Model\AttributeMetadataDataBuilder;
use Magento\Customer\Api\Data\OptionDataBuilder;
use Magento\Customer\Api\Data\ValidationRuleDataBuilder;

/**
 * Class AbstractTest
 */
class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testAddAttributesToForm()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $objectManager->get('Magento\Framework\View\DesignInterface')->setDefaultDesignTheme();
        $arguments = [
            $objectManager->get('Magento\Backend\Block\Template\Context'),
            $objectManager->get('Magento\Backend\Model\Session\Quote'),
            $objectManager->get('Magento\Sales\Model\AdminOrder\Create'),
            $objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface'),
            $objectManager->get('Magento\Framework\Data\FormFactory'),
            $objectManager->get('Magento\Framework\Reflection\DataObjectProcessor')
        ];

        /** @var $block \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm */
        $block = $this->getMockForAbstractClass(
            'Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm',
            $arguments
        );
        $block->setLayout($objectManager->create('Magento\Framework\View\Layout'));

        $method = new \ReflectionMethod(
            'Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm',
            '_addAttributesToForm'
        );
        $method->setAccessible(true);

        /** @var $formFactory \Magento\Framework\Data\FormFactory */
        $formFactory = $objectManager->get('Magento\Framework\Data\FormFactory');
        $form = $formFactory->create();
        $fieldset = $form->addFieldset('test_fieldset', []);
        /** @var \Magento\Customer\Model\AttributeMetadataDataBuilder $attributeBuilder */
        $attributeBuilder = $objectManager->create('Magento\Customer\Model\AttributeMetadataDataBuilder');
        $dateAttribute = $attributeBuilder->setAttributeCode('date')
            ->setBackendType('datetime')
            ->setFrontendInput('date')
            ->setFrontendLabel('Date')
            ->create();
        $attributes = ['date' => $dateAttribute];
        $method->invoke($block, $attributes, $fieldset);

        $element = $form->getElement('date');
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
