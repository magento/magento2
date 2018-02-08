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
}
