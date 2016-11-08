<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\TestCase;

use Magento\Widget\Test\Fixture\Widget;

/**
 * Steps:
 * 1. Login to the backend.
 * 2. Open Content > Widgets.
 * 3. Click Add Widget.
 * 4. Fill settings data according dataset.
 * 5. Click button Continue.
 * 6. Fill widget data according dataset.
 * 7. Perform all assertions.
 *
 * @group Widget
 * @ZephyrId MAGETWO-27916
 */
class CreateWidgetEntityTest extends AbstractCreateWidgetEntityTest
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S3';
    /* end tags */

    /**
     * Cache data.
     *
     * @var array
     */
    private $caches = [];

    /**
     * Create for New Widget.
     *
     * @param Widget $widget
     * @param array $additionalWidgets
     * @param array $caches [optional]
     * @return array
     */
    public function test(Widget $widget, array $additionalWidgets = [], array $caches = [])
    {
        // Preconditions
        $this->caches = $caches;
        $this->adjustCacheSettings();

        // Steps
        $this->widgetInstanceIndex->open();
        $this->widgetInstanceIndex->getPageActionsBlock()->addNew();
        $this->widgetInstanceNew->getWidgetForm()->fill($widget);
        $this->widgetInstanceEdit->getPageActionsBlock()->save();

        foreach ($additionalWidgets as $key => $additionalWidget) {
            $additionalWidget = $this->fixtureFactory->createByCode('widget', ['dataset' => $additionalWidget]);
            $additionalWidget->persist();
            $additionalWidgets[$key] = $additionalWidget;
        }

        return ['additionalWidgets' => $additionalWidgets];
    }

    /**
     * Adjust cache settings.
     *
     * @return void
     */
    private function adjustCacheSettings()
    {
        $this->cache->flush();
        foreach ($this->caches as $cacheType => $cacheStatus) {
            if ($cacheStatus === 'Disabled') {
                $this->cache->disableCache($cacheType);
            }
        }
        if (in_array('Invalidated', $this->caches)) {
            $this->cmsIndex->open();
        }
    }
    
    /**
     * Enable and flush all cache.
     *
     * return void
     */
    public function tearDown()
    {
        parent::tearDown();
        if (!empty($this->caches)) {
            $this->cache->enableCache();
        }
    }
}
