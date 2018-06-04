<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Transactions search form.
 */
class SearchForm extends Form
{
    /**
     * Search button selector.
     *
     * @var string
     */
    private $searchButton = '[type=submit]';

    /**
     * Search form container locator.
     *
     * @var string
     */
    private $searchContainer = '#MainWin';

    /**
     * Search for transactions.
     *
     * @return void
     */
    public function search()
    {
        $this->browser->find($this->searchButton)->click();
    }

    /**
     * @inheritdoc
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        // ensure search form container is loaded.
        $this->waitForElementVisible($this->searchContainer);

        return parent::fill($fixture, $element);
    }
}
