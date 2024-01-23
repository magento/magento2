<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

/**
 * Pop-up for Login as Customer button then Login as Customer is not allowed.
 */
class NotAllowedPopup extends Template
{
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
     * @param ConfigInterface $config
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigInterface $config,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $layout = $this->json->unserialize(parent::getJsLayout());

        $layout['components']['lac-not-allowed-popup']['title'] = __('Login as Customer not enabled');
        $layout['components']['lac-not-allowed-popup']['content'] = __(
            'The user has not enabled the "Allow remote shopping assistance" functionality. '
            . 'Contact the customer to discuss this user configuration.'
        );

        return $this->json->serialize($layout);
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if (!$this->config->isEnabled()) {
            return '';
        }
        return parent::toHtml();
    }
}
