<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Persistent\Helper\Data as PersistentHelper;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var PersistentHelper
     */
    private $persistentHelper;

    /**
     * @param PersistentHelper $persistentHelper
     */
    public function __construct(PersistentHelper $persistentHelper)
    {
        $this->persistentHelper = $persistentHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $isCheckboxVisible = $this->persistentHelper->isEnabled() && $this->persistentHelper->isRememberMeEnabled();
        $isCheckboxChecked = $this->persistentHelper->isRememberMeCheckedDefault();
        return [
            'persistenceConfig' => [
                'isRememberMeCheckboxVisible' => $isCheckboxVisible,
                'isRememberMeCheckboxChecked' => $isCheckboxChecked,
            ],
        ];
    }
}
