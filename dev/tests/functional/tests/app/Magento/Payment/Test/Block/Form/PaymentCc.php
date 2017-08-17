<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Block\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Form for filling credit card data.
 */
class PaymentCc extends Form
{
    /**
     * Fill credit card form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        unset($data['payment_code']);
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);

        return $this;
    }
}
