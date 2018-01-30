<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

use \Magento\Framework\File\Csv;

/**
 * Class Context
 */
class Context
{
    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var Csv
     */
    private $csvReader;

    /**
     * @param FixtureManager $fixtureManager
     * @param Csv $csvReader
     */
    public function __construct(FixtureManager $fixtureManager, Csv $csvReader)
    {
        $this->fixtureManager = $fixtureManager;
        $this->csvReader = $csvReader;
    }

    /**
     * @return FixtureManager
     */
    public function getFixtureManager()
    {
        return $this->fixtureManager;
    }

    /**
     * @return Csv
     */
    public function getCsvReader()
    {
        return $this->csvReader;
    }
}
