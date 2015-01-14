<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Store\Edit\Form;

use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

/**
 * Class GroupForm
 * Form for New Store Group creation
 */
class GroupForm extends Form
{
    /**
     * Website name selector in dropdown
     *
     * @var string
     */
    protected $website = '//option[contains(.,"%s")]';

    /**
     * Check that Website visible in Website dropdown
     *
     * @param string $websiteName
     * @return bool
     */
    public function isWebsiteVisible($websiteName)
    {
        return $this->_rootElement->find(sprintf($this->website, $websiteName), Locator::SELECTOR_XPATH)->isVisible();
    }
}
