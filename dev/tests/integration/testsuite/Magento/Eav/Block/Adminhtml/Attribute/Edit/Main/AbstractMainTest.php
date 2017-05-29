<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain
 */
namespace Magento\Eav\Block\Adminhtml\Attribute\Edit\Main;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractMainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDefaultDesignTheme();
        $entityType = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class)
            ->getEntityType('customer');
        $model = $objectManager->create(\Magento\Customer\Model\Attribute::class);
        $model->setEntityTypeId($entityType->getId());
        $objectManager->get(\Magento\Framework\Registry::class)->register('entity_attribute', $model);

        $block = $this->getMockForAbstractClass(
            \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain::class,
            [
                $objectManager->get(\Magento\Backend\Block\Template\Context::class),
                $objectManager->get(\Magento\Framework\Registry::class),
                $objectManager->get(\Magento\Framework\Data\FormFactory::class),
                $objectManager->get(\Magento\Eav\Helper\Data::class),
                $objectManager->get(\Magento\Config\Model\Config\Source\YesnoFactory::class),
                $objectManager->get(\Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory::class),
                $objectManager->get(\Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker::class)
            ]
        )->setLayout(
            $objectManager->create(\Magento\Framework\View\Layout::class)
        );

        $method = new \ReflectionMethod(
            \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain::class,
            '_prepareForm'
        );
        $method->setAccessible(true);
        $method->invoke($block);

        $element = $block->getForm()->getElement('default_value_date');
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
