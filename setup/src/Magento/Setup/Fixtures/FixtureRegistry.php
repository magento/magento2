<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Registry for fixtures
 */
namespace Magento\Setup\Fixtures;

class FixtureRegistry
{

    /**
     * List of fixtures applied to the application
     *
     * @var Fixture[]
     */
    private $fixtures = [];

    /**
     * @param array $fixtures
     */
    public function __construct(array $fixtures = [])
    {
        $this->fixtures = $fixtures;
    }

    /**
     * Get fixtures
     *
     * @return string[]
     */
    public function getFixtures() :array
    {
        return $this->fixtures;
    }

}
