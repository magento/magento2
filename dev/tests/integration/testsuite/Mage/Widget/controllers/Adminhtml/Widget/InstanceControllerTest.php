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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Widget
 */
class Mage_Widget_Adminhtml_Widget_InstanceControllerTest extends Mage_Adminhtml_Utility_Controller
{
    protected function setUp()
    {
        parent::setUp();

        $this->getRequest()->setParam('type', 'Mage_Cms_Block_Widget_Page_Link');
        $this->getRequest()->setParam('package_theme', 'default-default');
    }

    public function testEditAction()
    {
        $this->dispatch('admin/widget_instance/edit');
        $this->assertContains('<option value="Mage_Cms_Block_Widget_Page_Link" selected="selected">',
            $this->getResponse()->getBody()
        );
    }

    public function testBlocksAction()
    {
        $this->dispatch('admin/widget_instance/blocks');
        $this->assertStringStartsWith('<select name="block" id=""', $this->getResponse()->getBody());
    }

    public function testTemplateAction()
    {
        $this->dispatch('admin/widget_instance/template');
        $this->assertStringStartsWith('<select name="template" id=""', $this->getResponse()->getBody());
    }
}
