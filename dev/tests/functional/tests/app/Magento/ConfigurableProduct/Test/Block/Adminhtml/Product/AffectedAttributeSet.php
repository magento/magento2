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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Block\Widget\Form as ParentForm;

/**
 * Class AffectedAttributeSet
 * Choose affected attribute set dialog popup window
 */
class AffectedAttributeSet extends ParentForm
{
    /**
     * 'Confirm' button locator
     *
     * @var string
     */
    protected $confirmButton = '//button[contains(@id,"confirm-button")]';

    /**
     * Locator buttons new name attribute set
     *
     * @var string
     */
    protected $affectedAttributeSetNew = '#affected-attribute-set-new';

    /**
     * Fill popup form
     *
     * @param FixtureInterface $product
     * @param Element|null $element [optional]
     * @return $this
     */
    public function fill(FixtureInterface $product, Element $element = null)
    {
        $affectedAttributeSet = $product->getData('affected_attribute_set');

        if ($affectedAttributeSet) {
            $fields = ['new_attribute_set_name' => $affectedAttributeSet];
            $mapping = $this->dataMapping($fields);

            $this->_rootElement->find($this->affectedAttributeSetNew)->click();
            $this->_fill($mapping, $element);
        }

        return $this;
    }

    /**
     * Click confirm button
     *
     * @return void
     */
    public function confirm()
    {
        $this->_rootElement->find($this->confirmButton, Locator::SELECTOR_XPATH)->click();
    }
}
