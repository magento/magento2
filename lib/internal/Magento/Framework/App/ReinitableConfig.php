<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * @inheritdoc
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class ReinitableConfig extends MutableScopeConfig implements ReinitableConfigInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function reinit()
    {
        $this->clean();
        return $this;
    }
}
