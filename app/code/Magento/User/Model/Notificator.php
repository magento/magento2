<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\Spi\NotificatorInterface;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\DeploymentConfig;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Email\Model\BackendTemplate;

/**
 * @inheritDoc
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Notificator implements NotificatorInterface
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DeploymentConfig
     */
    private $deployConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param TransportBuilder $transportBuilder
     * @param ConfigInterface $config
     * @param DeploymentConfig $deployConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ConfigInterface $config,
        DeploymentConfig $deployConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
        $this->deployConfig = $deployConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Send a notification.
     *
     * @param string $templateConfigId
     * @param array $templateVars
     * @param string $toEmail
     * @param string $toName
     * @throws MailException
     *
     * @return void
     */
    private function sendNotification(
        string $templateConfigId,
        array $templateVars,
        string $toEmail,
        string $toName
    ): void {
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->config->getValue($templateConfigId))
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

    /**
     * @inheritDoc
     */
    public function sendForgotPassword(UserInterface $user): void
    {
        try {
            $this->sendNotification(
                'admin/emails/forgot_email_template',
                [
                    'user' => $user,
                    'store' => $this->storeManager->getStore(
                        Store::DEFAULT_STORE_ID
                    )
                ],
                $user->getEmail(),
                $user->getFirstName().' '.$user->getLastName()
            );
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function sendCreated(UserInterface $user): void
    {
        $toEmails = [];
        $generalEmail = $this->config->getValue(
            'trans_email/ident_general/email'
        );
        if ($generalEmail) {
            $toEmails[] = $generalEmail;
        }
        if ($adminEmail = $this->deployConfig->get('user_admin_email')) {
            $toEmails[] = $adminEmail;
        }

        try {
            foreach ($toEmails as $toEmail) {
                $this->sendNotification(
                    'admin/emails/new_user_notification_template',
                    [
                        'user' => $user,
                        'store' => $this->storeManager->getStore(
                            Store::DEFAULT_STORE_ID
                        )
                    ],
                    $toEmail,
                    __('Administrator')->getText()
                );
            }
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function sendUpdated(UserInterface $user, array $changed): void
    {
        $email = $user->getEmail();
        if ($user instanceof User) {
            $email = $user->getOrigData('email');
        }

        try {
            $this->sendNotification(
                'admin/emails/user_notification_template',
                [
                    'user' => $user,
                    'store' => $this->storeManager->getStore(
                        Store::DEFAULT_STORE_ID
                    ),
                    'changes' => implode(', ', $changed)
                ],
                $email,
                $user->getFirstName().' '.$user->getLastName()
            );
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }
}
