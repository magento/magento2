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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Adminhtml\Block\Sales\Order\Create\Form\AbstractForm
 */
namespace Magento\Adminhtml\Block\Sales\Order\Create\Form;

class AbstractTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testAddAttributesToForm()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_ADMINHTML)
            ->setDefaultDesignTheme();
        $arguments = array(
            $objectManager->get('Magento\Data\Form\Factory'),
            $objectManager->get('Magento\Adminhtml\Model\Session\Quote'),
            $objectManager->get('Magento\Adminhtml\Model\Sales\Order\Create'),
            $objectManager->get('Magento\Core\Helper\Data'),
            $objectManager->get('Magento\Backend\Block\Template\Context'),
        );
        /** @var $block \Magento\Adminhtml\Block\Sales\Order\Create\Form\AbstractForm */
        $block = $this
            ->getMockForAbstractClass('Magento\Adminhtml\Block\Sales\Order\Create\Form\AbstractForm', $arguments);
        $block->setLayout($objectManager->create('Magento\Core\Model\Layout'));

        $method = new \ReflectionMethod(
            'Magento\Adminhtml\Block\Sales\Order\Create\Form\AbstractForm', '_addAttributesToForm');
        $method->setAccessible(true);

        /** @var $formFactory \Magento\Data\Form\Factory */
        $formFactory = $objectManager->get('Magento\Data\Form\Factory');
        $form = $formFactory->create();
        $fieldset = $form->addFieldset('test_fieldset', array());
        $arguments = array(
            'data' => array(
                'attribute_code' => 'date',
                'backend_type' => 'datetime',
                'frontend_input' => 'date',
                'frontend_label' => 'Date',
            )
        );
        $dateAttribute = $objectManager->create('Magento\Customer\Model\Attribute', $arguments);
        $attributes = array('date' => $dateAttribute);
        $method->invoke($block, $attributes, $fieldset);

        $element = $form->getElement('date');
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
