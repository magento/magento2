<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class FormPageActions
 * Page actions block on page
 */
class FormPageActions extends \Magento\Catalog\Test\Block\Adminhtml\Product\FormPageActions
{
    // @codingStandardsIgnoreStart
    /**
     * Selector for "Affected Attribute Set" modal window
     *
     * @var string
     */
    protected $affectedAttributeSetForm = '.product_form_product_form_configurable_attribute_set_handler_modal [data-role="focusable-scope"]';
    // @codingStandardsIgnoreEnd

    /**
     * Click on "Save" button
     *
     * @param FixtureInterface|null $product [optional]
     * @return void
     */
    public function save(FixtureInterface $product = null)
    {
        parent::save();
        $affectedAttributeSetForm = $this->getAffectedAttributeSetForm();
        if ($affectedAttributeSetForm->isVisible()) {
            $affectedAttributeSetForm->fill($product)->confirm();
        }
    }

    /**
     * Get "Choose Affected Attribute Set" form
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AffectedAttributeSet
     */
    protected function getAffectedAttributeSetForm()
    {
        return $this->blockFactory->create(
            \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AffectedAttributeSet::class,
            ['element' => $this->browser->find($this->affectedAttributeSetForm)]
        );
    }
}
