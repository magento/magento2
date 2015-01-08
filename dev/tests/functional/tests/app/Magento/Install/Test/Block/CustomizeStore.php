<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Install\Test\Block;

use Mtf\Block\Form;
use Mtf\Client\Element\SimpleElement;
use Mtf\Client\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Customize Your Store block.
 */
class CustomizeStore extends Form
{
    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='next']";

    /**
     * First field selector
     *
     * @var string
     */
    protected $firstField = '[ng-model*="language"]';

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Ensure the form is loaded and fill the root form
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->waitForElementVisible($this->firstField);
        return parent::fill($fixture, $element);
    }
}
