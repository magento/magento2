<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class Fixture
 */
abstract class Fixture
{
    /**
     * @var int
     */
    protected $priority;

    /**
     * @var FixtureModel
     */
    protected $fixtureModel;

    /**
     * @param FixtureModel $fixtureModel
     */
    public function __construct(FixtureModel $fixtureModel)
    {
        $this->fixtureModel = $fixtureModel;
    }

    /**
     * Execute fixture
     *
     * @return void
     */
    abstract public function execute();

    /**
     * Get fixture action description
     *
     * @return string
     */
    abstract public function getActionTitle();

    /**
     * Introduce parameters labels
     *
     * @return array
     */
    abstract public function introduceParamLabels();

    /**
     * Get fixture priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
