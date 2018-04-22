<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Mail\AttachmentMessageInterface;

class AttachmentTransportBuilder extends TransportBuilder
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * AttachmentTransportBuilder constructor.
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param ReadFactory $readFactory
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        ReadFactory $readFactory,
        TransportBuilder $transportBuilder
    ) {
        if (!$message instanceof AttachmentMessageInterface) {
            throw new \InvalidArgumentException('message must be an implementation of AttachmentMessageInterface');
        }

        parent::__construct($templateFactory, $message, $senderResolver, $objectManager, $mailTransportFactory);
        $this->transportBuilder = $transportBuilder;
        $this->readFactory = $readFactory;
    }

    /**
     * Add a file joined to the email
     *
     * @param string $dir is the dir where the file is located
     * @param string $fileName is the name of the file to join
     * @param string $fileType is the type of the file, eg: application/pdf
     * @param string $joinedFileName is the name given to the joined file
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function attachFile($dir, $fileName, $fileType, $joinedFileName)
    {
        $file = $this->readFactory->create($dir);
        $this->transportBuilder->message->createAttachment(
            $file->readFile($fileName),
            $fileType,
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $joinedFileName
        );

        return $this;
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     * @return TransportBuilder
     */
    public function addCc($address, $name = '')
    {
        return $this->transportBuilder->addCc($address, $name);
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     * @return TransportBuilder
     */
    public function addTo($address, $name = '')
    {
        return $this->transportBuilder->addTo($address, $name);
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     * @return TransportBuilder
     */
    public function addBcc($address)
    {
        return $this->transportBuilder->addBcc($address);
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     * @return TransportBuilder
     */
    public function setReplyTo($email, $name = null)
    {
        return $this->transportBuilder->setReplyTo($email, $name);
    }

    /**
     * Set mail from address
     *
     * @param string|array $from
     * @return TransportBuilder
     */
    public function setFrom($from)
    {
        return $this->transportBuilder->setFrom($from);
    }

    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     * @return TransportBuilder
     */
    public function setTemplateIdentifier($templateIdentifier)
    {
        return $this->transportBuilder->setTemplateIdentifier($templateIdentifier);
    }

    /**
     * Set template model
     *
     * @param string $templateModel
     * @return TransportBuilder
     */
    public function setTemplateModel($templateModel)
    {
        return $this->transportBuilder->setTemplateModel($templateModel);
    }

    /**
     * Set template vars
     *
     * @param array $templateVars
     * @return TransportBuilder
     */
    public function setTemplateVars($templateVars)
    {
        return $this->transportBuilder->setTemplateVars($templateVars);
    }

    /**
     * Set template options
     *
     * @param array $templateOptions
     * @return TransportBuilder
     */
    public function setTemplateOptions($templateOptions)
    {
        return $this->transportBuilder->setTemplateOptions($templateOptions);
    }

    /**
     * Get mail transport
     *
     * @return \Magento\Framework\Mail\TransportInterface
     */
    public function getTransport()
    {
        return $this->transportBuilder->getTransport();
    }

    /**
     * Reset object state
     *
     * @return TransportBuilder
     */
    protected function reset()
    {
        return $this->transportBuilder->reset();
    }

    /**
     * Get template
     *
     * @return \Magento\Framework\Mail\TemplateInterface
     */
    protected function getTemplate()
    {
        return $this->transportBuilder->getTemplate();
    }

    /**
     * Prepare message
     *
     * @return TransportBuilder
     */
    protected function prepareMessage()
    {
        return $this->transportBuilder->prepareMessage();
    }
}
