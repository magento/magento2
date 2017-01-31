<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Web configuration block.
 */
class WebConfiguration extends Form
{
    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='next']";

    /**
     * 'Advanced Options' locator.
     *
     * @var string
     */
    protected $advancedOptions = "[ng-click*='advanced']";

    /**
     * Admin URI check.
     *
     * @var string
     */
    protected $adminUriCheck = '#admin';

    /**
     * 'Advanced Options' block locator.
     *
     * @var string
     */
    protected $extendedConfig = '[ng-show="config.advanced.expanded"]';

    /**
     * Fill web configuration form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        $webConfiguration = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'db') !== 0 && strpos($key, 'store') !== 0) {
                $webConfiguration[$key] = $value;
            }
        }
        $mapping = $this->dataMapping($webConfiguration);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next)->click();
    }

    /**
     * Click on 'Advanced Options' button.
     *
     * @return void
     */
    public function clickAdvancedOptions()
    {
        if (!$this->_rootElement->find($this->extendedConfig)->isVisible()) {
            $this->_rootElement->find($this->advancedOptions)->click();
        }
    }

    public function getAdminUriCheck()
    {
        return $this->_rootElement->find($this->adminUriCheck)->getAttribute('ng-init');
    }
}
