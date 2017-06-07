<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\System\Store\Edit\Form\Website
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $registryData = [
            'store_type' => 'website',
            'store_data' => $objectManager->create(\Magento\Store\Model\Website::class),
            'store_action' => 'add',
        ];
        foreach ($registryData as $key => $value) {
            $objectManager->get(\Magento\Framework\Registry::class)->register($key, $value);
        }

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $objectManager->get(\Magento\Framework\View\LayoutInterface::class);

        $this->_block = $layout->createBlock(\Magento\Backend\Block\System\Store\Edit\Form\Website::class);

        $this->_block->toHtml();
    }

    protected function tearDown()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('store_type');
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('store_data');
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('store_action');
    }

    public function testPrepareForm()
    {
        $form = $this->_block->getForm();
        $this->assertEquals('website_fieldset', $form->getElement('website_fieldset')->getId());
        $this->assertEquals('website_name', $form->getElement('website_name')->getId());
        $this->assertEquals('website', $form->getElement('store_type')->getValue());
    }
}
