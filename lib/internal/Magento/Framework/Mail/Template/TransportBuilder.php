<?php
/**
 * Mail Template Transport Builder
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Mail\Template;

class TransportBuilder
{
    /**
     * Template Identifier
     *
     * @var string
     */
    protected $templateIdentifier;

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
     * @var \Magento\Framework\Mail\MessageInterface
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
    protected $_mailTransportFactory;

    /**
     * @param FactoryInterface $templateFactory
     * @param \Magento\Framework\Mail\MessageInterface $message
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Mail\TransportInterfaceFactory $mailTransportFactory
     */
    public function __construct(
        \Magento\Framework\Mail\Template\FactoryInterface $templateFactory,
        \Magento\Framework\Mail\MessageInterface $message,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Mail\TransportInterfaceFactory $mailTransportFactory
    ) {
        $this->templateFactory = $templateFactory;
        $this->message = $message;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
        $this->_mailTransportFactory = $mailTransportFactory;
    }

    /**
     * @return \Magento\Framework\Mail\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \Magento\Framework\Mail\MessageInterface $message
     * @return $this
     */
    public function setMessage(\Magento\Framework\Mail\MessageInterface $message)
    {
        $this->message  = $message;
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
        $mailTransport = $this->_mailTransportFactory->create(['message' => clone $this->message]);
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
        $this->message = $this->objectManager->create('Magento\Framework\Mail\Message');
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
        return $this->templateFactory->get(
            $this->templateIdentifier
        )->setVars(
            $this->templateVars
        )->setOptions(
            $this->templateOptions
        );
    }

    /**
     * Prepare message
     *
     * @return $this
     */
    protected function prepareMessage()
    {
        $template = $this->getTemplate();
        $types = [
            \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT => \Magento\Framework\Mail\MessageInterface::TYPE_TEXT,
            \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML => \Magento\Framework\Mail\MessageInterface::TYPE_HTML,
        ];

        $body = $template->processTemplate();
        $this->message->setMessageType(
            $types[$template->getType()]
        )->setBody(
            $body
        )->setSubject(
            $template->getSubject()
        );

        return $this;
    }
}
