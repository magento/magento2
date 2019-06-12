<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
namespace Magento\Setup\Test\Block\SelectVersion;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Block\SelectVersion\OtherComponentsGrid\Item;

<<<<<<< HEAD
=======
/**
 * Perform OtherComponentsGrid block.
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
     * @param $packages
     */
    public function setVersions(array $packages)
=======
     * Set version of the packages.
     *
     * @param array $packages
     * @return void
     */
    public function setVersions(array $packages) : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
    public function getSelectedPackages()
=======
    public function getSelectedPackages() : array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return $this->selectedPackages;
    }

    /**
<<<<<<< HEAD
     * @param int $count
     */
    public function setItemsPerPage($count)
=======
     * Set pager size.
     *
     * @param int $count
     * @return void
     */
    public function setItemsPerPage(int $count) : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $this->_rootElement->find($this->perPage, Locator::SELECTOR_CSS, 'select')->setValue($count);
    }

    /**
<<<<<<< HEAD
     * @param ElementInterface $element
     * @return Item
     */
    private function getComponentRow($element)
=======
     * Get component block.
     *
     * @param ElementInterface $element
     * @return Item
     */
    private function getComponentRow(ElementInterface $element) : Item
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return $this->blockFactory->create(
            Item::class,
            ['element' => $element]
        );
    }
}
