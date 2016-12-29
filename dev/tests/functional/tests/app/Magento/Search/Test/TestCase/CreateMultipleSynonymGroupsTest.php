<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Steps:
 * 1. Create a set of synonym groups.
 * 2. Perform all assertions.
 *
 * @group Search
 * @ZephyrId MAGETWO-47264
 */
class CreateMultipleSynonymGroupsTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Injection data.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create Synonym Groups.
     *
     * @param array $synonymGroups
     * @return array
     */
    public function test(array $synonymGroups)
    {
        $groups = [];
        foreach ($synonymGroups as $key => $dataset) {
            $groups[$key] = $this->fixtureFactory->createByCode('synonymGroup', ['dataset' => $dataset]);
            $groups[$key]->persist();
        }

        return [
            'synonymGroups' => $groups,
        ];
    }

    /**
     * Delete all synonym groups.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\Search\Test\TestStep\DeleteAllSynonymGroupsStep::class)->run();
    }
}
