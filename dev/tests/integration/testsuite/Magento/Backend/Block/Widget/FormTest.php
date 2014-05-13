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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Widget;

/**
 * Test class for \Magento\Backend\Block\Widget\Form
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testSetFieldset()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            'Magento\Framework\View\DesignInterface'
        )->setArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->setDefaultDesignTheme();
        $layout = $objectManager->create('Magento\Framework\View\Layout');
        $formBlock = $layout->addBlock('Magento\Backend\Block\Widget\Form');
        $fieldSet = $objectManager->create('Magento\Framework\Data\Form\Element\Fieldset');
        $arguments = array(
            'data' => array(
                'attribute_code' => 'date',
                'backend_type' => 'datetime',
                'frontend_input' => 'date',
                'frontend_label' => 'Date'
            )
        );
        $attributes = array($objectManager->create('Magento\Eav\Model\Entity\Attribute', $arguments));
        $method = new \ReflectionMethod('Magento\Backend\Block\Widget\Form', '_setFieldset');
        $method->setAccessible(true);
        $method->invoke($formBlock, $attributes, $fieldSet);
        $fields = $fieldSet->getElements();

        $this->assertEquals(1, count($fields));
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Date', $fields[0]);
        $this->assertNotEmpty($fields[0]->getDateFormat());
    }
}
