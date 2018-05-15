<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Block\SelectVersion;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Block\SelectVersion\OtherComponentsGrid\Item;

/**
 * Perform OtherComponentsGrid block.
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
     * Set version of the packages.
     *
     * @param array $packages
     * @return void
     */
    public function setVersions(array $packages) : void
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
     * Returns selected packages.
     *
     * @return array
     */
    public function getSelectedPackages() : array
    {
        return $this->selectedPackages;
    }

    /**
     * Set pager size.
     *
     * @param int $count
     * @return void
     */
    public function setItemsPerPage(int $count) : void
    {
        $this->_rootElement->find($this->perPage, Locator::SELECTOR_CSS, 'select')->setValue($count);
    }

    /**
     * Get component block.
     *
     * @param ElementInterface $element
     * @return Item
     */
    private function getComponentRow(ElementInterface $element) : Item
    {
        return $this->blockFactory->create(
            Item::class,
            ['element' => $element]
        );
    }
}
