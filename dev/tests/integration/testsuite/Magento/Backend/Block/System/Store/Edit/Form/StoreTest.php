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
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class StoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\System\Store\Edit\Form\Store
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $registryData = array(
            'store_type' => 'store',
            'store_data' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Store\Model\Store'
            ),
            'store_action' => 'add'
        );
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        foreach ($registryData as $key => $value) {
            $objectManager->get('Magento\Framework\Registry')->register($key, $value);
        }

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');

        $this->_block = $layout->createBlock('Magento\Backend\Block\System\Store\Edit\Form\Store');

        $this->_block->toHtml();
    }

    protected function tearDown()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('store_type');
        $objectManager->get('Magento\Framework\Registry')->unregister('store_data');
        $objectManager->get('Magento\Framework\Registry')->unregister('store_action');
    }

    public function testPrepareForm()
    {
        $form = $this->_block->getForm();
        $this->assertEquals('store_fieldset', $form->getElement('store_fieldset')->getId());
        $this->assertEquals('store_name', $form->getElement('store_name')->getId());
        $this->assertEquals('store', $form->getElement('store_type')->getValue());
    }
}
