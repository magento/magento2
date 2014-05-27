<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class Bundle
 * Bundle options section
 */
class Bundle extends Tab
{
    /**
     * 'Create New Option' button
     *
     * @var string
     */
    protected $addNewOption = '#add_new_option';

    /**
     * Bundle options block
     *
     * @var string
     */
    protected $bundleOptionBlock = '#bundle_option_';

    /**
     * Get bundle options block
     *
     * @param int $blockNumber
     * @return \Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option
     */
    protected function getBundleOptionBlock($blockNumber)
    {
        return Factory::getBlockFactory()->getMagentoBundleAdminhtmlCatalogProductEditTabBundleOption(
            $this->_rootElement->find($this->bundleOptionBlock . $blockNumber)
        );
    }

    /**
     * Fill bundle options
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        if (!isset($fields['bundle_selections'])) {
            return $this;
        }
        $bundleOptions = $this->prepareBundleOptions($fields['bundle_selections']['value']);
        $blocksNumber = 0;
        foreach ($bundleOptions as $bundleOption) {
            $this->_rootElement->find($this->addNewOption)->click();
            $bundleOptionsBlock = $this->getBundleOptionBlock($blocksNumber);
            $bundleOptionsBlock->fillBundleOption($bundleOption, $this->_rootElement);
            $blocksNumber++;
        }

        return $this;
    }

    /**
     * Update bundle options
     *
     * @param array $fields
     * @param Element|null $element
     * @return void
     */
    public function updateFormTab(array $fields, Element $element = null)
    {
        if (!isset($fields['bundle_selections'])) {
            return;
        }
        $bundleOptions = $this->prepareBundleOptions($fields['bundle_selections']['value']);
        $blocksNumber = 0;
        foreach ($$bundleOptions as $bundleOption) {
            $bundleOptionsBlock = $this->getBundleOptionBlock($blocksNumber, $element);
            $bundleOptionsBlock->expand();
            $bundleOptionsBlock->updateBundleOption($bundleOption, $element);
            $blocksNumber++;
        }
    }

    /**
     * Prepare Bundle Options array from preset
     *
     * @param array $bundleSelections
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function prepareBundleOptions(array $bundleSelections)
    {
        if (!isset($bundleSelections['preset'])) {
            return $bundleSelections;
        }

        $preset = $bundleSelections['preset'];
        $products = $bundleSelections['products'];
        foreach ($preset['items'] as & $item) {
            foreach ($item['assigned_products'] as $productIncrement => & $selection) {
                if (!isset($products[$productIncrement])) {
                    throw new \InvalidArgumentException(
                        sprintf('Not sufficient number of products for bundle preset: %s', $preset['name'])
                    );
                }
                /** @var $fixture CatalogProductSimple */
                $fixture = $products[$productIncrement];
                $selection['search_data']['name'] = $fixture->getName();
                $selection['data']['product_id']['value'] = $fixture->getId();
            }
        }
        return $preset['items'];
    }
}
