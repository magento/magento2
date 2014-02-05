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
 * @package     Magento_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

class AbstractTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testAddAttributesToForm()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $objectManager->get('Magento\View\DesignInterface')
            ->setDefaultDesignTheme();
        $arguments = array(
            $objectManager->get('Magento\Backend\Block\Template\Context'),
            $objectManager->get('Magento\Backend\Model\Session\Quote'),
            $objectManager->get('Magento\Sales\Model\AdminOrder\Create'),
            $objectManager->get('Magento\Data\FormFactory'),
        );

        /** @var $block \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm */
        $block = $this
            ->getMockForAbstractClass('Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm', $arguments);
        $block->setLayout($objectManager->create('Magento\Core\Model\Layout'));

        $method = new \ReflectionMethod(
            'Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm', '_addAttributesToForm');
        $method->setAccessible(true);

        /** @var $formFactory \Magento\Data\FormFactory */
        $formFactory = $objectManager->get('Magento\Data\FormFactory');
        $form = $formFactory->create();
        $fieldset = $form->addFieldset('test_fieldset', array());
        $arguments = array(
            'data' => array(
                'attribute_code' => 'date',
                'backend_type' => 'datetime',
                'front_end_input' => 'date',
                'frontend_label' => 'Date',
            )
        );
        $dateAttribute = $objectManager->create('\Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata', $arguments);
        $attributes = array('date' => $dateAttribute);
        $method->invoke($block, $attributes, $fieldset);

        $element = $form->getElement('date');
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
