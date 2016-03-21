<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\TestCase;

use Magento\Widget\Test\Fixture\Widget;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceEdit;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceNew;
use Magento\Mtf\TestCase\Injectable;

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
     * Injection data.
     *
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @param WidgetInstanceNew $widgetInstanceNew
     * @param WidgetInstanceEdit $widgetInstanceEdit
     * @return void
     */
    public function __inject(
        WidgetInstanceIndex $widgetInstanceIndex,
        WidgetInstanceNew $widgetInstanceNew,
        WidgetInstanceEdit $widgetInstanceEdit
    ) {
        $this->widgetInstanceIndex = $widgetInstanceIndex;
        $this->widgetInstanceNew = $widgetInstanceNew;
        $this->widgetInstanceEdit = $widgetInstanceEdit;
    }

    /**
     * Delete all widgets.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create('Magento\Widget\Test\TestStep\DeleteAllWidgetsStep')->run();
    }
}
