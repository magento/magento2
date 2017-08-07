<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Controller;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Contact module base controller
 */
abstract class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Recipient email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = ConfigInterface::XML_PATH_EMAIL_RECIPIENT;

    /**
     * Sender email config path
     */
    const XML_PATH_EMAIL_SENDER = ConfigInterface::XML_PATH_EMAIL_SENDER;

    /**
     * Email template config path
     */
    const XML_PATH_EMAIL_TEMPLATE = ConfigInterface::XML_PATH_EMAIL_TEMPLATE;

    /**
     * Enabled config path
     */
    const XML_PATH_ENABLED = ConfigInterface::XML_PATH_ENABLED;

    /**
     * @var ConfigInterface
     * @since 2.2.0
     */
    private $contactsConfig;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig
    ) {
        parent::__construct($context);
        $this->contactsConfig = $contactsConfig;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->contactsConfig->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }
}
