<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Widget\Test\Fixture\Widget;

/**
 * Steps:
 * 1. Create widgets.
 * 2. Perform all assertions.
 *
 * @group Widget
 * @ZephyrId MAGETWO-61801
 */
class CreateWidgetsEntityTest extends Injectable
{
    /* tags */
    const SEVERITY = 'S3';
    /* end tags */

    /**
     * Create multiple widgets.
     *
     * @param array $widgets
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function test(array $widgets, FixtureFactory $fixtureFactory)
    {
        /** @var Widget[] $widgetInstances */
        $widgetInstances = [];
        // Preconditions
        foreach ($widgets as $widget) {
            $widget = $fixtureFactory->createByCode('widget', ['dataset' => $widget]);
            $widget->persist();
            $widgetInstances[] = $widget;
        }

        return ['widgets' => $widgetInstances];
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
