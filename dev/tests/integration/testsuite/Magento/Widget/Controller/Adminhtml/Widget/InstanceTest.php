<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Cms\Block\Widget\Page\Link;
use Magento\Framework\App\Area;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Widget\Model\Widget\Instance;

/**
 * @magentoAppArea adminhtml
 */
class InstanceTest extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Bootstrap::getInstance()
            ->loadArea(FrontNameResolver::AREA_CODE);

        $theme = Bootstrap::getObjectManager()->get(
            DesignInterface::class
        )->setDefaultDesignTheme()->getDesignTheme();
        $type = Link::class;
        /** @var $model Instance */
        $model = Bootstrap::getObjectManager()->create(
            Instance::class
        );
        $code = $model->setType($type)->getWidgetReference('type', $type, 'code');
        $this->getRequest()->setParam('code', $code);
        $this->getRequest()->setParam('theme_id', $theme->getId());
    }

    /**
     * @return void
     */
    public function testEditAction(): void
    {
        $this->dispatch('backend/admin/widget_instance/edit');
        $this->assertMatchesRegularExpression(
            '/<option value="cms_page_link".*?selected="selected"\>/is',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @return void
     */
    public function testBlocksAction(): void
    {
        Bootstrap::getInstance()
            ->loadArea(Area::AREA_FRONTEND);
        $theme = Bootstrap::getObjectManager()->get(
            DesignInterface::class
        )->setDefaultDesignTheme()->getDesignTheme();
        $this->getRequest()->setParam('theme_id', $theme->getId());
        $this->dispatch('backend/admin/widget_instance/blocks');
        $this->assertStringStartsWith('<select name="block" id=""', $this->getResponse()->getBody());
    }

    /**
     * @return void
     */
    public function testTemplateAction(): void
    {
        $this->getRequest()->setMethod('POST');
        $this->dispatch('backend/admin/widget_instance/template');
        $this->assertStringStartsWith('<select name="template" id=""', $this->getResponse()->getBody());
    }
}
