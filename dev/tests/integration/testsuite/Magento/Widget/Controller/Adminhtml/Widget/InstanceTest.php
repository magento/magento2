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
namespace Magento\Widget\Controller\Adminhtml\Widget;

/**
 * @magentoAppArea adminhtml
 */
class InstanceTest extends \Magento\Backend\Utility\Controller
{
    protected function setUp()
    {
        parent::setUp();

        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDefaultDesignTheme()->getDesignTheme();
        $type = 'Magento\Cms\Block\Widget\Page\Link';
        /** @var $model \Magento\Widget\Model\Widget\Instance */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Widget\Instance'
        );
        $code = $model->setType($type)->getWidgetReference('type', $type, 'code');
        $this->getRequest()->setParam('code', $code);
        $this->getRequest()->setParam('theme_id', $theme->getId());
    }

    public function testEditAction()
    {
        $this->dispatch('backend/admin/widget_instance/edit');
        $this->assertContains('<option value="cms_page_link" selected="selected">', $this->getResponse()->getBody());
    }

    public function testBlocksAction()
    {
        $this->dispatch('backend/admin/widget_instance/blocks');
        $this->assertStringStartsWith('<select name="block" id=""', $this->getResponse()->getBody());
    }

    public function testTemplateAction()
    {
        $this->dispatch('backend/admin/widget_instance/template');
        $this->assertStringStartsWith('<select name="template" id=""', $this->getResponse()->getBody());
    }
}
