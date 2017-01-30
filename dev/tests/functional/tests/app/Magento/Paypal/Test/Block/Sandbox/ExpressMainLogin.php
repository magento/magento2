<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

/**
 * Login to PayPal side within new or old login form.
 */
class ExpressMainLogin extends Form
{
    /**
     * Express Login Block selector.
     *
     * @var string
     */
    protected $expressLogin = '[name=login]';

    /**
     * Old Express Login Block selector.
     *
     * @var string
     */
    protected $expressOldLogin = '#loginBox';

    /**
     * Determines whether new login form or old is shown.
     *
     * @return \Magento\Paypal\Test\Block\Sandbox\ExpressLogin|\Magento\Paypal\Test\Block\Sandbox\ExpressOldLogin
     */
    public function getLoginBlock()
    {
        if ($this->_rootElement->find($this->expressLogin)->isVisible()) {
            return $this->blockFactory->create(
                'Magento\Paypal\Test\Block\Sandbox\ExpressLogin',
                ['element' => $this->_rootElement->find($this->expressLogin)]
            );
        }
        return $this->blockFactory->create(
            'Magento\Paypal\Test\Block\Sandbox\ExpressOldLogin',
            ['element' => $this->_rootElement->find($this->expressOldLogin)]
        );
    }
}
