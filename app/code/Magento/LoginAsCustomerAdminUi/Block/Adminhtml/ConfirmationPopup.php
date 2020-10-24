<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;

/**
 * Login confirmation pop-up
 *
 * @api
 * @since 100.4.0
 */
class ConfirmationPopup extends Template
{
    /**
     * @var StoreOptions
     */
    private $storeOptions;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Template\Context $context
     * @param StoreOptions $storeOptions
     * @param ConfigInterface $config
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        StoreOptions $storeOptions,
        ConfigInterface $config,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeOptions = $storeOptions;
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * @inheritdoc
     * @since 100.4.0
     */
    public function getJsLayout()
    {
        $layout = $this->json->unserialize(parent::getJsLayout());
        $showStoreViewOptions = $this->config->isStoreManualChoiceEnabled();

        $layout['components']['lac-confirmation-popup']['title'] = $showStoreViewOptions
            ? __('Login as Customer: Select Store View')
            : __('You are about to Login as Customer');
        $layout['components']['lac-confirmation-popup']['content'] =
            __('Actions taken while in "Login as Customer" will affect actual customer data.');

        $layout['components']['lac-confirmation-popup']['showStoreViewOptions'] = $showStoreViewOptions;
        $layout['components']['lac-confirmation-popup']['storeViewOptions'] = $showStoreViewOptions
            ? $this->storeOptions->toOptionArray()
            : [];

        return $this->json->serialize($layout);
    }

    /**
     * @inheritdoc
     * @since 100.4.0
     */
    public function toHtml()
    {
        if (!$this->config->isEnabled()) {
            return '';
        }
        return parent::toHtml();
    }
}
