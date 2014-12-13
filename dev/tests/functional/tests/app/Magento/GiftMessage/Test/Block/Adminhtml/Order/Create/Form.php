<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\Create;

use Mtf\Block\Form as ParentForm;
use Mtf\Client\Element;
use Mtf\Fixture\FixtureInterface;

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
     * @param Element|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        parent::fill($fixture, $element);
        $this->_rootElement->find($this->okButton)->click();
    }
}
