<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Plugin\Ui\Component\Form\Element;

use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Ui\Component\Form\Element\Select;

/**
 * Removes the website filter from the customer "Send Welcome Email From" store-view select
 * when customer account sharing is Global.
 *
 * In Global sharing scope a customer may be associated with any store view regardless of the
 * selected website, so the dropdown must list every store view.
 */
class DisableStoreViewWebsiteFilter
{
    /**
     * Field whose website filter must be disabled in Global scope.
     */
    private const FIELD_NAME = 'sendemail_store_id';

    /**
     * UI component namespace the field belongs to.
     */
    private const FORM_NAMESPACE = 'customer_form';

    /**
     * @var ConfigShare
     */
    private ConfigShare $configShare;

    /**
     * @param ConfigShare $configShare
     */
    public function __construct(ConfigShare $configShare)
    {
        $this->configShare = $configShare;
    }

    /**
     * Remove the website filter for the welcome-email store-view select in Global sharing scope.
     *
     * @param Select $subject
     * @return void
     */
    public function beforePrepare(Select $subject): void
    {
        if ($subject->getName() !== self::FIELD_NAME) {
            return;
        }
        if ($subject->getContext()->getNamespace() !== self::FORM_NAMESPACE) {
            return;
        }
        if ($this->configShare->isWebsiteScope()) {
            return;
        }
        $config = $subject->getData('config');
        if (isset($config['filterBy'])) {
            unset($config['filterBy']);
            $subject->setData('config', $config);
        }
    }
}
