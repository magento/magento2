<?php
/**
 * Mail Template Transport Builder
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mail\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * @api
 */
class TransportBuilder
{
    /**
     * Template Identifier
     *
     * @var string
     */
    protected $templateIdentifier;

    /**
     * Template Model
     *
     * @var string
     */
    protected $templateModel;

    /**
     * Template Variables
     *
     * @var array
     */
    protected $templateVars;

    /**
     * Template Options
     *
     * @var array
     */
    protected $templateOptions;

    /**
     * Mail Transport
     *
     * @var \Magento\Framework\Mail\TransportInterface
     */
    protected $transport;

    /**
     * Template Factory
     *
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Message
     *
     * @var \Magento\Framework\Mail\Message
     */
    protected $message;

    /**
     * Sender resolver
     *
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    protected $_senderResolver;

    /**
     * @var \Magento\Framework\Mail\TransportInterfaceFactory
     */
    protected $mailTransportFactory;

    /**
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory
    ) {
        $this->templateFactory = $templateFactory;
        $this->message = $message;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
        $this->mailTransportFactory = $mailTransportFactory;
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     * @return $this
     */
    public function addCc($address, $name = '')
    {
        $this->message->addCc($address, $name);
        return $this;
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     * @return $this
     */
    public function addTo($address, $name = '')
    {
        $this->message->addTo($address, $name);
        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     * @return $this
     */
    public function addBcc($address)
    {
        $this->message->addBcc($address);
        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     * @return $this
     */
    public function setReplyTo($email, $name = null)
    {
        $this->message->setReplyTo($email, $name);
        return $this;
    }

    /**
     * Set mail from address
     *
     * @param string|array $from
     * @return $this
     */
    public function setFrom($from)
    {
        $result = $this->_senderResolver->resolve($from);
        $this->message->setFrom($result['email'], $result['name']);
        return $this;
    }

    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     * @return $this
     */
    public function setTemplateIdentifier($templateIdentifier)
    {
        $this->templateIdentifier = $templateIdentifier;
        return $this;
    }

    /**
     * Set template model
     *
     * @param string $templateModel
     * @return $this
     */
    public function setTemplateModel($templateModel)
    {
        $this->templateModel = $templateModel;
        return $this;
    }

    /**
     * Set template vars
     *
     * @param array $templateVars
     * @return $this
     */
    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;
        return $this;
    }

    /**
     * Set template options
     *
     * @param array $templateOptions
     * @return $this
     */
    public function setTemplateOptions($templateOptions)
    {
        $this->templateOptions = $templateOptions;
        return $this;
    }

    /**
     * Get mail transport
     *
     * @return \Magento\Framework\Mail\TransportInterface
     */
    public function getTransport()
    {
        $this->prepareMessage();
        $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        $this->reset();

        return $mailTransport;
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset()
    {
        $this->message = $this->objectManager->create(\Magento\Framework\Mail\Message::class);
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }

    /**
     * Get template
     *
     * @return \Magento\Framework\Mail\TemplateInterface
     */
    protected function getTemplate()
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }

    /**
     * Prepare message.
     *
     * @return $this
     * @throws LocalizedException if template type is unknown
     */
    protected function prepareMessage()
    {
        $template = $this->getTemplate();
        $body = $template->processTemplate();
        switch ($template->getType()) {
            case TemplateTypesInterface::TYPE_TEXT:
                $this->message->setBodyText($body);
                break;

            case TemplateTypesInterface::TYPE_HTML:
                $this->message->setBodyHtml($body);
                break;

            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }
        $this->message->setSubject(html_entity_decode($template->getSubject(), ENT_QUOTES));
        return $this;
    }
}
