<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Persistent\Helper\Data as PersistentHelper;

/**
 * Class \Magento\Persistent\Model\CheckoutConfigProvider
 *
 * @since 2.0.0
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var PersistentHelper
     * @since 2.0.0
     */
    private $persistentHelper;

    /**
     * @param PersistentHelper $persistentHelper
     * @since 2.0.0
     */
    public function __construct(PersistentHelper $persistentHelper)
    {
        $this->persistentHelper = $persistentHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
