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

namespace Magento\ConfigurableProduct\Test\Block\Backend\Product;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable;

/**
 * Class AffectedAttributeSet
 * Choose affected attribute set dialog popup window
 */
class AffectedAttributeSet extends Block
{
    /**
     * Create new attribute set based on default
     *
     * @var string
     */
    protected $affectedAttributeSet = '[name=affected-attribute-set][value=new]';

    /**
     * New attribute set name
     *
     * @var string
     */
    protected $attributeSetName = '[name=new-attribute-set-name]';

    /**
     * 'Confirm' button
     *
     * @var string
     */
    protected $confirmButton = '[id*=confirm-button]';

    /**
     * Choose affected attribute set
     *
     * @param CatalogProductConfigurable $fixture
     */
    public function chooseAttributeSet(CatalogProductConfigurable $fixture)
    {
        $attributeSetName = $fixture->getAttributeSetName();
        if ($attributeSetName) {
            $this->_rootElement->find($this->affectedAttributeSet)->click();
            $this->_rootElement->find($this->attributeSetName)->setValue($attributeSetName);
        }
        $this->_rootElement->find($this->confirmButton)->click();
    }
}
