<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    protected $affectedAttributeSetForm = '//div[@data-role="affected-attribute-set-selector"]/ancestor::*[@data-role="modal"]';
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
            '\Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AffectedAttributeSet',
            ['element' => $this->browser->find($this->affectedAttributeSetForm, Locator::SELECTOR_XPATH)]
        );
    }
}
