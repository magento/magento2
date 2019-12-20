<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Example\ExampleFrontendUi\Block;

use Example\ExampleApi\Api\WelcomeMessageInterface;
use Magento\Framework\View\Element\Template;

/**
 * ExamlpleFrontUi block
 *
 * @api
 */
class Index extends Template
{
    /**
     * @var WelcomeMessageInterface
     */
    private $message;

    /**
     * @param Template\Context $context
     * @param WelcomeMessageInterface $message
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        WelcomeMessageInterface $message,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->message = $message;
    }

    /**
     * Get Welcome message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message->execute();
    }
}
