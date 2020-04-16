<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable;

use Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable;

class Stub extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName()
    {
        return 'stub';
    }
}
