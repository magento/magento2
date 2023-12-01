<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Layout;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Class AbstractTest
 *
 * Test cases to check custom attribute can be added successfully with the form
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testAddAttributesToForm()
    {
        $objectManager = Bootstrap::getObjectManager();
        Bootstrap::getInstance()
            ->loadArea(FrontNameResolver::AREA_CODE);
        $objectManager->get(DesignInterface::class)->setDefaultDesignTheme();
        $arguments = [
            $objectManager->get(Context::class),
            $objectManager->get(Quote::class),
            $objectManager->get(Create::class),
            $objectManager->get(PriceCurrencyInterface::class),
            $objectManager->get(FormFactory::class),
            $objectManager->get(DataObjectProcessor::class)
        ];

        /** @var $block AbstractForm */
        $block = $this->getMockForAbstractClass(
            AbstractForm::class,
            $arguments
        );
        $block->setLayout($objectManager->create(Layout::class));

        $method1 = new ReflectionMethod(
            AbstractForm::class,
            '_addAttributesToForm'
        );
        $method2 = new ReflectionMethod(
            AbstractForm::class,
            'getForm'
        );

        $form = $method2->invoke($block);
        $fieldset = $form->addFieldset('test_fieldset', []);
        /** @var AttributeMetadataInterfaceFactory $attributeMetadataFactory */
        $attributeMetadataFactory =
            $objectManager->create(AttributeMetadataInterfaceFactory::class);
        $dateAttribute = $attributeMetadataFactory->create()->setAttributeCode('date')
            ->setBackendType('datetime')
            ->setFrontendInput('date')
            ->setFrontendLabel('Date')
            ->setSortOrder(100);

        $textAttribute = $attributeMetadataFactory->create()->setAttributeCode('test_text')
            ->setBackendType('text')
            ->setFrontendInput('text')
            ->setFrontendLabel('Test Text')
            ->setSortOrder(200);

        $attributes = ['date' => $dateAttribute, 'test_text' => $textAttribute];
        $method1->invoke($block, $attributes, $fieldset);

        $element1 = $form->getElement('date');
        $this->assertNotNull($element1);
        $this->assertNotEmpty($element1->getDateFormat());
        $this->assertNotEmpty($element1->getSortOrder());
        $this->assertEquals($element1->getSortOrder(), 100);

        $element2 = $form->getElement('test_text');
        $this->assertNotNull($element2);
        $this->assertNotEmpty($element2->getSortOrder());
        $this->assertEquals($element2->getSortOrder(), 200);
    }
}
