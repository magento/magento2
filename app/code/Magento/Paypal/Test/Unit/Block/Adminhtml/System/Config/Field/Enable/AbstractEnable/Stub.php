<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
