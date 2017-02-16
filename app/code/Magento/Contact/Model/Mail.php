<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model;

use Magento\Contact\Api\ConfigInterface;
use Magento\Contact\Api\MailInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

class Mail implements MailInterface
{
    /**
     * @var ConfigInterface
     */
    private $contactsConfig;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    public function __construct(
        ConfigInterface $contactsConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation
    ) {
        $this->contactsConfig = $contactsConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    public function send($replyTo, $variables)
    {
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->contactsConfig->emailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => 'adminhtml',
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars($variables)
                ->setFrom($this->contactsConfig->emailSender())
                ->addTo($this->contactsConfig->emailRecipient())
                ->setReplyTo($replyTo)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }

}