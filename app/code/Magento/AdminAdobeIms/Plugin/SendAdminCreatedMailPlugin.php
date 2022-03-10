<?php

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\Notificator;

/**
 * @copyright  Copyright (c) 2021 TechDivision GmbH (https://www.techdivision.com)
 * @author     TechDivision Team Allstars <allstars@techdivision.com>
 * @link       https://www.techdivision.com/
 */
class SendAdminCreatedMailPlugin
{
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Repository
     */
    private Repository $assetRepo;

    /**
     * @var BackendUrlInterface
     */
    private BackendUrlInterface $backendUrl;

    /**
     * @param TransportBuilder $transportBuilder
     * @param ConfigInterface $config
     * @param ImsConfig $imsConfig
     * @param StoreManagerInterface $storeManager
     * @param Repository $assetRepo
     * @param BackendUrlInterface $backendUrl
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ConfigInterface $config,
        ImsConfig $imsConfig,
        StoreManagerInterface $storeManager,
        Repository $assetRepo,
        BackendUrlInterface $backendUrl
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
        $this->imsConfig = $imsConfig;
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
        $this->backendUrl = $backendUrl;
    }

    /**
     * @param Notificator $subject
     * @param null $result
     * @param UserInterface $user
     * @return void
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function afterSendCreated(Notificator $subject, $result, UserInterface $user): void
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
     * @throws MailException
     *
     * @return void
     */
    private function sendNotificationEmail(
        array $templateVars,
        string $toEmail,
        string $toName
    ): void {
        $transport = $this->transportBuilder
            ->setTemplateIdentifier('admin_emails_new_user_created_template')
            ->setTemplateModel(BackendTemplate::class)
            ->setTemplateOptions([
                'area' => FrontNameResolver::AREA_CODE,
                'store' => Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars($templateVars)
            ->setFrom(
                $this->config->getValue('admin/emails/forgot_email_identity')
            )
            ->addTo($toEmail, $toName)
            ->getTransport();
        $transport->sendMessage();
    }
}
