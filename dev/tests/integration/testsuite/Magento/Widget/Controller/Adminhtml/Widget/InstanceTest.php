<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Controller\Adminhtml\Widget;

/**
 * @magentoAppArea adminhtml
 */
class InstanceTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        parent::setUp();

        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDefaultDesignTheme()->getDesignTheme();
        $type = \Magento\Cms\Block\Widget\Page\Link::class;
        /** @var $model \Magento\Widget\Model\Widget\Instance */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Widget\Instance::class
        );
        $code = $model->setType($type)->getWidgetReference('type', $type, 'code');
        $this->getRequest()->setParam('code', $code);
        $this->getRequest()->setParam('theme_id', $theme->getId());
    }

    public function testEditAction()
    {
        $this->dispatch('backend/admin/widget_instance/edit');
        $this->assertRegExp(
            '/<option value="cms_page_link".*?selected="selected"\>/is',
            $this->getResponse()->getBody()
        );
    }

    public function testBlocksAction()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDefaultDesignTheme()->getDesignTheme();
        $this->getRequest()->setParam('theme_id', $theme->getId());
        $this->dispatch('backend/admin/widget_instance/blocks');
        $this->assertStringStartsWith('<select name="block" id=""', $this->getResponse()->getBody());
    }

    public function testTemplateAction()
    {
        $this->getRequest()->setMethod('POST');
        $this->dispatch('backend/admin/widget_instance/template');
        $this->assertStringStartsWith('<select name="template" id=""', $this->getResponse()->getBody());
    }
}
