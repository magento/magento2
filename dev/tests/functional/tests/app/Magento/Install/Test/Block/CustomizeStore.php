<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

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
    protected $next = "[ng-click*='checkModuleConstraints']";

    /**
     * Module configuration section.
     *
     * @var string
     */
    protected $moduleConfiguration = '.customize-your-store-advanced';

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
        $this->waitForElementVisible($this->moduleConfiguration);
        $data = $fixture->getData();
        $storeData = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'store') === 0) {
                $storeData[$key] = $value;
            }
        }
        $mapping = $this->dataMapping($storeData);
        $this->_fill($mapping, $element);

        return $this;
    }
}
