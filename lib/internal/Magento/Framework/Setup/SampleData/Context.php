<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

use \Magento\Framework\File\Csv;

/**
 * Constructor modification point for Magento\Framework\Setup\SampleData.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
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
