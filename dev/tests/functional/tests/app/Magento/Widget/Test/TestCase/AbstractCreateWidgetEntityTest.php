<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\TestCase;

use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceEdit;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;

/**
 * Test Creation for New Instance of WidgetEntity.
 */
abstract class AbstractCreateWidgetEntityTest extends Injectable
{
    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    protected $testStep;

    /**
     * WidgetInstanceIndex page.
     *
     * @var WidgetInstanceIndex
     */
    protected $widgetInstanceIndex;

    /**
     * WidgetInstanceNew page.
     *
     * @var WidgetInstanceNew
     */
    protected $widgetInstanceNew;

    /**
     * WidgetInstanceEdit page.
     *
     * @var WidgetInstanceEdit
     */
    protected $widgetInstanceEdit;

    /**
     * "Cache Management" Admin panel page.
     *
     * @var AdminCache
     */
    protected $cachePage;

    /**
     * Injection data.
     *
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @param WidgetInstanceNew $widgetInstanceNew
     * @param WidgetInstanceEdit $widgetInstanceEdit
     * @param AdminCache $adminCache
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        WidgetInstanceIndex $widgetInstanceIndex,
        WidgetInstanceNew $widgetInstanceNew,
        WidgetInstanceEdit $widgetInstanceEdit,
        AdminCache $adminCache,
        TestStepFactory $testStepFactory
    ) {
        $this->widgetInstanceIndex = $widgetInstanceIndex;
        $this->widgetInstanceNew = $widgetInstanceNew;
        $this->widgetInstanceEdit = $widgetInstanceEdit;
        $this->cachePage = $adminCache;
        $this->testStep = $testStepFactory;
    }

    /**
     * Delete all Widgets & flush the Cache.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create('Magento\Widget\Test\TestStep\DeleteAllWidgetsStep')->run();
        $this->flushCache();
    }

    /**
     * Flush Magento Cache in Admin panel.
     *
     * @return void
     */
    protected function flushCache()
    {
        $this->cachePage->open();
        $this->cachePage->getActionsBlock()->flushMagentoCache();
        $this->cachePage->getMessagesBlock()->waitSuccessMessage();
    }
}
