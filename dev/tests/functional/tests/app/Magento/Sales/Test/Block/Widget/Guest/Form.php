<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Widget\Guest;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Orders and Returns form search block.
 */
class Form extends \Magento\Mtf\Block\Form
{
    /**
     * Search button selector.
     *
     * @var string
     */
    protected $searchButtonSelector = '.action.submit';

    /**
     * Selector for loads form.
     *
     * @var string
     */
    protected $loadsForm = 'div[id*=oar] input';

    /**
     * Fill the form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @param bool $isSearchByEmail [optional]
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null, $isSearchByEmail = true)
    {
        /** @var OrderInjectable $fixture */
        /** @var Customer $customer */
        $customer = $fixture->getDataFieldConfig('customer_id')['source']->getCustomer();
        $data = [
            'order_id' => $fixture->getId(),
            'billing_last_name' => $customer->getLastname(),
        ];

        if ($isSearchByEmail) {
            $data['find_order_by'] = 'Email';
            $data['email_address'] = $customer->getEmail();
        } else {
            $data['find_order_by'] = 'ZIP Code';
            $data['billing_zip_code'] = $fixture->getDataFieldConfig('billing_address_id')['source']->getPostcode();
        }

        $fields = isset($data['fields']) ? $data['fields'] : $data;
        $mapping = $this->dataMapping($fields);

        $this->waitLoadForm();
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Wait while form is loading.
     *
     * @return void
     */
    protected function waitLoadForm()
    {
        $rootElement = $this->_rootElement;
        $selector = $this->loadsForm;
        $this->browser->waitUntil(
            function () use ($rootElement, $selector) {
                $inputs = $rootElement->getElements($selector);
                $i = 0;
                foreach ($inputs as $input) {
                    if ($input->isVisible()) {
                        ++$i;
                    }
                }
                return $i == 1 ? true : null;
            }
        );
    }

    /**
     * Submit search form.
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->searchButtonSelector, Locator::SELECTOR_CSS)->click();
    }
}
