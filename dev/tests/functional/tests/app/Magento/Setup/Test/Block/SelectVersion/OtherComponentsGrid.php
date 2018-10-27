<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
namespace Magento\Setup\Test\Block\SelectVersion;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Block\SelectVersion\OtherComponentsGrid\Item;

<<<<<<< HEAD
/**
 * Perform OtherComponentsGrid block.
 */
=======
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
     * Set version of the packages.
     *
     * @param array $packages
     * @return void
     */
    public function setVersions(array $packages) : void
=======
     * @param $packages
     */
    public function setVersions(array $packages)
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
    public function getSelectedPackages() : array
=======
    public function getSelectedPackages()
>>>>>>> upstream/2.2-develop
    {
        return $this->selectedPackages;
    }

    /**
<<<<<<< HEAD
     * Set pager size.
     *
     * @param int $count
     * @return void
     */
    public function setItemsPerPage(int $count) : void
=======
     * @param int $count
     */
    public function setItemsPerPage($count)
>>>>>>> upstream/2.2-develop
    {
        $this->_rootElement->find($this->perPage, Locator::SELECTOR_CSS, 'select')->setValue($count);
    }

    /**
<<<<<<< HEAD
     * Get component block.
     *
     * @param ElementInterface $element
     * @return Item
     */
    private function getComponentRow(ElementInterface $element) : Item
=======
     * @param ElementInterface $element
     * @return Item
     */
    private function getComponentRow($element)
>>>>>>> upstream/2.2-develop
    {
        return $this->blockFactory->create(
            Item::class,
            ['element' => $element]
        );
    }
}
