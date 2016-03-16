<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ThemeSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Css
 */
class Css
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var HeadStyle
     */
    private $headStyle;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param HeadStyle $headStyle
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        HeadStyle $headStyle
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->headStyle = $headStyle;
    }

    /**
     * @param array $fixtures
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileId => $cssFile) {
            $this->headStyle->add($this->fixtureManager->getFixture($fileId), $cssFile);
        }
    }
}
