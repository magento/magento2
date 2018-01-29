<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\SelectVersion;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Block\SelectVersion\OtherComponentsGrid\Item;

class OtherComponentsGrid extends Block
{
    /**
     * @var string
     */
    private $itemComponent = '//tr[contains(@ng-repeat,"component") and //td[contains(.,"%s")]]';

    /**
     * @param $packages
     */
    public function setVersions(array $packages)
    {
        foreach ($packages as $package) {
            $this->getComponentRow($package['name'])->setVersion($package['version']);
        }
    }

    /**
     * @param string $componentName
     * @return Item
     */
    private function getComponentRow($componentName)
    {
        $selector = sprintf($this->itemComponent, $componentName);
        return $this->blockFactory->create(
            Item::class,
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }
}
