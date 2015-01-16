<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain
 */
namespace Magento\Eav\Block\Adminhtml\Attribute\Edit\Main;

class AbstractTest extends \PHPUnit_Framework_TestCase
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
        $objectManager->get('Magento\Framework\View\DesignInterface')
            ->setDefaultDesignTheme();
        $entityType = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Eav\Model\Config')
            ->getEntityType('customer');
        $model = $objectManager->create('Magento\Customer\Model\Attribute');
        $model->setEntityTypeId($entityType->getId());
        $objectManager->get('Magento\Framework\Registry')->register('entity_attribute', $model);

        $block = $this->getMockForAbstractClass(
            'Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain',
            [
                $objectManager->get('Magento\Backend\Block\Template\Context'),
                $objectManager->get('Magento\Framework\Registry'),
                $objectManager->get('Magento\Framework\Data\FormFactory'),
                $objectManager->get('Magento\Eav\Helper\Data'),
                $objectManager->get('Magento\Backend\Model\Config\Source\YesnoFactory'),
                $objectManager->get('Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory'),
                $objectManager->get('Magento\Eav\Model\Entity\Attribute\Config')
            ]
        )->setLayout(
            $objectManager->create('Magento\Framework\View\Layout')
        );

        $method = new \ReflectionMethod(
            'Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain',
            '_prepareForm'
        );
        $method->setAccessible(true);
        $method->invoke($block);

        $element = $block->getForm()->getElement('default_value_date');
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
