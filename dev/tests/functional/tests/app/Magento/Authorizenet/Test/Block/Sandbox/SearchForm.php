<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

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
     * Search for transactions.
     *
     * @return void
     */
    public function search()
    {
        $this->browser->find($this->searchButton)->click();
    }
}
