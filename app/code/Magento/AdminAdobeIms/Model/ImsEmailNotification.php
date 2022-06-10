<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\ConfigInterface;
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use Magento\Store\Model\Store;

class ImsEmailNotification
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
     * @var AssetRepo
     */
    private AssetRepo $assetRepo;

    /**
     * @param TransportBuilder $transportBuilder
     * @param ConfigInterface $config
     * @param AssetRepo $assetRepo
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ConfigInterface $config,
        AssetRepo $assetRepo
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Send email notification
     *
     * @param string $emailTemplate
     * @param array $templateVars
     * @param string $toEmail
     * @param string $toName
     * @return void
     * @throws LocalizedException
     *
     * @throws MailException
     */
    public function sendNotificationEmail(
        string $emailTemplate,
        array $templateVars,
        string $toEmail,
        string $toName
    ): void {

        $templateVars = $this->addTemplateVars($templateVars);

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($emailTemplate)
            ->setTemplateModel(BackendTemplate::class)
            ->setTemplateOptions([
                'area' => FrontNameResolver::AREA_CODE,
                'store' => Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars($templateVars)
            ->setFromByScope(
                $this->config->getValue('adobe_ims/email/new_user_email_identity'),
                Store::DEFAULT_STORE_ID
            )
            ->addTo($toEmail, $toName)
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     * Add additional (default) template variables like current_year and logo if not already set
     *
     * @param array $templateVars
     * @return array
     */
    private function addTemplateVars(array $templateVars): array
    {
        if (!isset($templateVars['current_year'])) {
            $templateVars['current_year'] = date('Y');
        }

        if (!isset($templateVars['logo_url'])) {
            $logo = $this->assetRepo->getUrlWithParams(
                'Magento_AdminAdobeIms::images/adobe-commerce-light.png',
                []
            );

            $templateVars['logo_url'] = $logo;
        }

        return $templateVars;
    }
}
