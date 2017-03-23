<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Store\Edit\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

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
