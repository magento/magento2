<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Newsletter\Test\Block\Adminhtml\Template;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Newsletter template preview.
 */
class Preview extends Block
{
    /**
     * IFrame locator.
     *
     * @var string
     */
    protected $iFrame = '#preview_iframe';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '//ancestor::body/div[@data-role="loader"]';

    /**
     * Get page content text
     *
     * @return string
     */
    public function getPageContent()
    {
        $selector = $this->loader;
        $browser = $this->browser;
        $this->browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector, Locator::SELECTOR_XPATH);
                return $element->isVisible() == false ? true : null;
            }
        );
        $this->browser->switchToFrame(new Locator($this->iFrame));
        return $this->_rootElement->getText();
    }
}
