<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\SelectVersion;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Block\SelectVersion\OtherComponentsGrid\Item;

/**
 * Other components grid block.
 */
class OtherComponentsGrid extends Block
{
    /**
     * @var string
     */
    private $itemComponent = '//tr[contains(@ng-repeat,"component") and ./td[contains(.,"%s")]]';

    /**
     * @var string
     */
    private $perPage = '#perPage';

    /**
     * @var array
     */
    private $selectedPackages = [];

    /**
     * @param $packages
     */
    public function setVersions(array $packages)
    {
        foreach ($packages as $package) {
            $selector = sprintf($this->itemComponent, $package['name']);
            $elements = $this->_rootElement->getElements($selector, Locator::SELECTOR_XPATH);
            foreach ($elements as $element) {
                $row = $this->getComponentRow($element);
                $row->setVersion($package['version']);
                $this->selectedPackages[$row->getPackageName()] = $package['version'];
            }
        }
    }

    /**
     * Set maximum compatible sample-data version for each sample-data module.
     *
     * @param string $versionPattern
     * @throws \Exception
     */
    public function chooseSampleDataVersions(string $versionPattern)
    {
        foreach ($this->getSampleDataComponentsTableRows() as $row) {
            $row = $this->getComponentRow($row);
            $this->setMaxVersionToRowSelect($row, $versionPattern);
        }
    }

    /**
     * Returns selected packages.
     *
     * @return array
     */
    public function getSelectedPackages()
    {
        return $this->selectedPackages;
    }

    /**
     * @param int $count
     */
    public function setItemsPerPage($count)
    {
        $this->_rootElement->find($this->perPage, Locator::SELECTOR_CSS, 'select')->setValue($count);
    }

    /**
     * @param ElementInterface $element
     * @return Item
     */
    private function getComponentRow($element)
    {
        return $this->blockFactory->create(
            Item::class,
            ['element' => $element]
        );
    }

    /**
     * Get sample data components rows.
     *
     * @return ElementInterface[]
     */
    private function getSampleDataComponentsTableRows()
    {
        $selector = sprintf($this->itemComponent, 'sample-data');
        return $this->_rootElement->getElements($selector, Locator::SELECTOR_XPATH);
    }

    /**
     * Set version that corresponds to the maximum compatible version.
     *
     * @param Item $row
     * @param string $versionPattern
     * @throws \Exception
     */
    private function setMaxVersionToRowSelect(Item $row, string $versionPattern)
    {
        $allowedOptions = [];
        $versionRegexpPattern = $this->convertVersionFixtureToRegexp($versionPattern);
        foreach ($row->getAvailableVersions() as $optionText) {
            if (preg_match('#' . $versionRegexpPattern . '#', $optionText)) {
                preg_match('#^(?<version>[\d+\.\w-]+)#', $optionText, $match);
                $allowedOptions[$optionText] = $match['version'];
            }
        }

        if (!empty($allowedOptions)) {
            uasort(
                $allowedOptions,
                function ($versionOne, $versionTwo) {
                    return version_compare($versionOne, $versionTwo, '<');
                }
            );

            $version = reset($allowedOptions);
            $row->setVersion($version);
            $this->selectedPackages[$row->getPackageName()] = $version;
        }
    }

    /**
     * Convert version fixture to regexp pattern.
     *
     * Example 100.1.* to 100\.1\.\d+
     *
     * @param string $sampleDataFixture
     * @return string
     * @throws \Exception
     */
    private function convertVersionFixtureToRegexp(string $sampleDataFixture)
    {
        if (!preg_match('#\d+\.\d+\.[\d*.\w]+#', $sampleDataFixture)) {
            throw new \Exception('Wrong format for the sample data version fixture. Example: 100.1.*.');
        }
        return str_replace(['*', '.'], ['\d+', '\.'], $sampleDataFixture);
    }
}
