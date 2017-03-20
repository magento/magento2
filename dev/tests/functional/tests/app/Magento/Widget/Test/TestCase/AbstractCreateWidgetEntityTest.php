<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\TestCase;

use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceEdit;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Test Creation for New Instance of WidgetEntity.
 */
abstract class AbstractCreateWidgetEntityTest extends Injectable
{
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
     * CmsIndex page.
     *
     * @var WidgetInstanceIndex
     */
    protected $cmsIndex;

    /**
     * Handle cache for tests executions.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Injection data.
     *
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @param WidgetInstanceNew $widgetInstanceNew
     * @param WidgetInstanceEdit $widgetInstanceEdit
     * @param CmsIndex $cmsIndex
     * @param Cache $cache
     * @return void
     */
    public function __inject(
        WidgetInstanceIndex $widgetInstanceIndex,
        WidgetInstanceNew $widgetInstanceNew,
        WidgetInstanceEdit $widgetInstanceEdit,
        CmsIndex $cmsIndex,
        Cache $cache
    ) {
        $this->widgetInstanceIndex = $widgetInstanceIndex;
        $this->widgetInstanceNew = $widgetInstanceNew;
        $this->widgetInstanceEdit = $widgetInstanceEdit;
        $this->cmsIndex = $cmsIndex;
        $this->cache = $cache;
    }

    /**
     * Delete all widgets.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\Widget\Test\TestStep\DeleteAllWidgetsStep::class)->run();
    }
}
