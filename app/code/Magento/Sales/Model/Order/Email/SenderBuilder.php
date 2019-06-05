<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

/**
 * Sender Builder
 */
class SenderBuilder
{
    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var TransportBuilderFactory
     */
    protected $transportBuilderFactory;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilderFactory $transportBuilderFactory
     * @param TransportBuilderByStore $transportBuilderByStore
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilderFactory $transportBuilderFactory,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->transportBuilderFactory = $transportBuilderFactory;
    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {
        $transportBuilder = $this->transportBuilderFactory->create();

        $this->configureEmailTemplate($transportBuilder);

        $transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $transportBuilder->addBcc($email);
            }
        }

        $transport = $transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * Prepare and send copy email message
     *
     * @return void
     */
    public function sendCopyTo()
    {
        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'copy') {
            foreach ($copyTo as $email) {
                $transportBuilder = $this->transportBuilderFactory->create();

                $this->configureEmailTemplate($transportBuilder);

                $transportBuilder->addTo($email);

                $transport = $transportBuilder->getTransport();
                $transport->sendMessage();
            }
        }
    }

    /**
     * Configure email template
     *
     * @param TransportBuilder $transportBuilder
     * @return void
     */
    protected function configureEmailTemplate($transportBuilder)
    {
        $transportBuilder->setTemplateIdentifier($this->templateContainer->getTemplateId());
        $transportBuilder->setTemplateOptions($this->templateContainer->getTemplateOptions());
        $transportBuilder->setTemplateVars($this->templateContainer->getTemplateVars());
        $transportBuilder->setFromByScope(
            $this->identityContainer->getEmailIdentity(),
            $this->identityContainer->getStore()->getId()
        );
    }
}
