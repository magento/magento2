<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
namespace Magento\Setup\Test\Block\SelectVersion\OtherComponentsGrid;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block for each component.
 */
class Item extends Block
{
    /**
     * CSS selector for version element.
     *
     * @var string
     */
    private $version = '[ng-change*="setComponentVersion"]';

    /**
     * CSS selector for package name element.
     *
     * @var string
     */
    private $packageName = 'td:nth-child(2)';

    /**
     * Set version for particular component.
     *
     * @param string $version
     */
<<<<<<< HEAD
    public function setVersion($version)
=======
    public function setVersion(string $version)
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $this->_rootElement->find($this->version, Locator::SELECTOR_CSS, 'select')->setValue($version);
    }

    /**
     * Returns package name of element.
     *
     * @return array|string
     */
    public function getPackageName()
    {
        return $this->_rootElement->find($this->packageName)->getText();
    }
}
