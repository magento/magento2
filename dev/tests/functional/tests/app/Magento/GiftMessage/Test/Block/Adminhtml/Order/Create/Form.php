<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\Create;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Block\Form as ParentForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Form
 * Backend item form for gift message
 */
class Form extends ParentForm
{
    /**
     * Selector for 'OK' button.
     *
     * @var string
     */
    protected $okButton = '#gift_options_ok_button';

    /**
     * Fill backend GiftMessage item form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        parent::fill($fixture, $element);
        $this->_rootElement->find($this->okButton)->click();
    }
}
