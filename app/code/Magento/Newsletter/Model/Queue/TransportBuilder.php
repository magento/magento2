<?php
/** @noinspection SenselessMethodDuplicationInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection PhpUndefinedClassInspection */
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model\Queue;

use Magento\Email\Model\AbstractTemplate;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MailAddressConverter;
use Magento\Framework\Mail\MailAddressList;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class TransportBuilder
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Template data
     *
     * @var array
     */
    protected $templateData = [];

    /**
     * Param that used for storing all message data until it will be used
     *
     * @var array
     */
    private $messageData = [];

    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;

    /**
     * @var MailAddressConverter|null
     */
    private $mailAddressConverter;

    /**
     * TransportBuilder constructor
     *
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param MessageInterfaceFactory|null $messageFactory
     * @param EmailMessageInterfaceFactory|null $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory|null $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory|null $mimePartInterfaceFactory
     * @param MailAddressConverter|null $mailAddressConverter
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory = null,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory = null,
        MimePartInterfaceFactory $mimePartInterfaceFactory = null,
        MailAddressConverter $mailAddressConverter = null
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory,
            $emailMessageInterfaceFactory,
            $mimeMessageInterfaceFactory,
            $mimePartInterfaceFactory,
            $mailAddressConverter
        );
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory ?: $this->objectManager
            ->get(EmailMessageInterfaceFactory::class);
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory ?: $this->objectManager
            ->get(MimeMessageInterfaceFactory::class);
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory ?: $this->objectManager
            ->get(MimePartInterfaceFactory::class);
        $this->mailAddressConverter = $mailAddressConverter ?: $this->objectManager
            ->get(MailAddressConverter::class);
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     * @throws MailException
     */
    public function addCc($address, $name = '')
    {
        $this->getMailAddresses('cc', $address, $name);

        return $this;
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     * @throws MailException
     */
    public function addTo($address, $name = '')
    {
        $this->getMailAddresses('to', $address, $name);

        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     *
     * @return $this
     * @throws MailException
     */
    public function addBcc($address)
    {
        $this->getMailAddresses('bcc', $address);

        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     *
     * @return $this
     * @throws MailException
     */
    public function setReplyTo($email, $name = null)
    {

        $this->getMailAddresses('replyTo', $email, $name);

        return $this;
    }

    /**
     * Set mail from address
     *
     * @param string|array $from
     *
     * @return $this
     * @throws MailException
     * @see setFromByScope()
     *
     * @deprecated This function sets the from address but does not provide
     * a way of setting the correct from addresses based on the scope.
     */
    public function setFrom($from)
    {
        return $this->setFromByScope($from);
    }

    /**
     * Set mail from address by scopeId
     *
     * @param string|array $from
     * @param string|int $scopeId
     *
     * @return $this
     * @throws MailException
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->getMailAddresses('from', $result['email'], $result['name']);

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function reset()
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;

        return $this;
    }

    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     */
    public function setTemplateData($data)
    {
        $this->templateData = $data;

        return $this;
    }

    /**
     * Sets up template filter
     *
     * @param AbstractTemplate $template
     *
     * @return void
     */
    protected function setTemplateFilter(AbstractTemplate $template)
    {
        if (isset($this->templateData['template_filter'])) {
            $template->setTemplateFilter($this->templateData['template_filter']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareMessage()
    {
        /** @var AbstractTemplate|TemplateInterface $template */
        $template = $this->getTemplate()->setData($this->templateData);
        $this->setTemplateFilter($template);
        $content = $template->getProcessedTemplate($this->templateVars);
        $this->messageData['subject'] = $template->getSubject();

        $mimePart = $this->mimePartInterfaceFactory->create(
            ['content' => $content]
        );
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => [$mimePart]]
        );

        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }

    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param string|array $emailOrList
     * @param string|null $name
     *
     * @return void
     * @throws MailException
     */
    private function getMailAddresses(string $addressType, $emailOrList, ?string $name = null): void
    {
        if (is_array($emailOrList)) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $this->mailAddressConverter->convertMany($emailOrList)
            );

            return;
        }
        $this->messageData[$addressType][] = $this->mailAddressConverter->convert($emailOrList, $name);
    }
}
