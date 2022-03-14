<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;

class AdminNotificationService
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var AssetRepo
     */
    private AssetRepo $assetRepo;

    /**
     * @var BackendUrlInterface
     */
    private BackendUrlInterface $backendUrl;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @param ImsConfig $imsConfig
     * @param AssetRepo $assetRepo
     * @param BackendUrlInterface $backendUrl
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param ConfigInterface $config
     */
    public function __construct(
        ImsConfig $imsConfig,
        AssetRepo $assetRepo,
        BackendUrlInterface $backendUrl,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ConfigInterface $config
    ) {
        $this->imsConfig = $imsConfig;
        $this->assetRepo = $assetRepo;
        $this->backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
    }

    /**
     * Send a welcome mail to created admin user
     *
     * @param UserInterface $user
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendWelcomeMailToAdminUser(UserInterface $user): void
    {
        if (!$this->imsConfig->enabled()) {
            return;
        }

        $logo = $this->assetRepo->getUrlWithParams(
            'Magento_AdminAdobeIms::images/adobe-commerce-light.png',
            []
        );

        $backendUrl = $this->backendUrl->getUrl('admin');

        $this->sendNotificationEmail(
            [
                'user' => $user,
                'store' => $this->storeManager->getStore(
                    Store::DEFAULT_STORE_ID
                ),
                'cta_link' => $backendUrl,
                'logo_url' => $logo,
                'current_year' => date('Y'),
            ],
            $user->getEmail(),
            $user->getFirstName() . ' ' . $user->getLastName()
        );
    }

    /**
     * Send welcome e-mail to created user.
     *
     * @param array $templateVars
     * @param string $toEmail
     * @param string $toName
     * @return void
     * @throws LocalizedException
     *
     * @throws MailException
     */
    private function sendNotificationEmail(
        array $templateVars,
        string $toEmail,
        string $toName
    ): void {
        $emailTemplate = $this->getEmailTemplate();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($emailTemplate)
            ->setTemplateModel(BackendTemplate::class)
            ->setTemplateOptions([
                'area' => FrontNameResolver::AREA_CODE,
                'store' => Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars($templateVars)
            ->setFromByScope(
                $this->config->getValue('admin/emails/forgot_email_identity'),
                Store::DEFAULT_STORE_ID
            )
            ->addTo($toEmail, $toName)
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     * Get Email Template
     *
     * @return string
     */
    private function getEmailTemplate(): string
    {
        return $this->imsConfig->getEmailTemplateForNewAdminUsers();
    }
}
