<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Class FormPageActions
 * Page actions block on page
 */
class FormPageActions extends \Magento\Catalog\Test\Block\Adminhtml\Product\FormPageActions
{
    /**
     * Selector for "Affected Attribute Set" popup form
     *
     * @var string
     */
    protected $affectedAttributeSetForm = '//ancestor::body//div[div[@id="affected-attribute-set-form"]]';

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
            ['element' => $this->_rootElement->find($this->affectedAttributeSetForm, Locator::SELECTOR_XPATH)]
        );
    }
}
