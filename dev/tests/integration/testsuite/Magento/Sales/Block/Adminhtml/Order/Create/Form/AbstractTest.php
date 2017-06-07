<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;

/**
 * Class AbstractTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

        $objectManager->get(\Magento\Framework\View\DesignInterface::class)->setDefaultDesignTheme();
        $arguments = [
            $objectManager->get(\Magento\Backend\Block\Template\Context::class),
            $objectManager->get(\Magento\Backend\Model\Session\Quote::class),
            $objectManager->get(\Magento\Sales\Model\AdminOrder\Create::class),
            $objectManager->get(\Magento\Framework\Pricing\PriceCurrencyInterface::class),
            $objectManager->get(\Magento\Framework\Data\FormFactory::class),
            $objectManager->get(\Magento\Framework\Reflection\DataObjectProcessor::class)
        ];

        /** @var $block \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm */
        $block = $this->getMockForAbstractClass(
            \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm::class,
            $arguments
        );
        $block->setLayout($objectManager->create(\Magento\Framework\View\Layout::class));

        $method = new \ReflectionMethod(
            \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm::class,
            '_addAttributesToForm'
        );
        $method->setAccessible(true);

        /** @var $formFactory \Magento\Framework\Data\FormFactory */
        $formFactory = $objectManager->get(\Magento\Framework\Data\FormFactory::class);
        $form = $formFactory->create();
        $fieldset = $form->addFieldset('test_fieldset', []);
        /** @var \Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory $attributeMetadataFactory */
        $attributeMetadataFactory =
            $objectManager->create(\Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory::class);
        $dateAttribute = $attributeMetadataFactory->create()->setAttributeCode('date')
            ->setBackendType('datetime')
            ->setFrontendInput('date')
            ->setFrontendLabel('Date');
        $attributes = ['date' => $dateAttribute];
        $method->invoke($block, $attributes, $fieldset);

        $element = $form->getElement('date');
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
