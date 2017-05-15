<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Database form.
 */
class Database extends Form
{
    /**
     * 'Test connection successful.' message.
     *
     * @var string
     */
    protected $successConnectionMessage = ".text-success";

    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='testConnection']";

    /**
     * Fill database form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        $dbData = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'db') === 0) {
                $dbData[$key] = $value;
            }
        }
        $mapping = $this->dataMapping($dbData);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Get 'Test connection successful.' message.
     *
     * @return string
     */
    public function getSuccessConnectionMessage()
    {
        return $this->_rootElement->find($this->successConnectionMessage, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }
}
