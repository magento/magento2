<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Package;

use InvalidArgumentException;
use Magento\Framework\App\Area;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\AppInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Locale;
use Magento\Store\Model\Config\StoreView;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Deployment Package Locale Resolver class
 */
class LocaleResolver
{
    /**
     * Parameter to force deploying certain languages for the admin, without any users having configured them yet.
     */
    const ADMIN_LOCALES_FOR_DEPLOY = 'admin_locales_for_deploy';

    /**
     * @var StoreView
     */
    private $storeView;

    /**
     * @var UserCollectionFactory
     */
    private $userCollFactory;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|null
     */
    private $usedStoreLocales;

    /**
     * @var array|null
     */
    private $usedAdminLocales;

    /**
     * LocaleResolver constructor.
     *
     * @param StoreView $storeView
     * @param UserCollectionFactory $userCollectionFactory
     * @param DeploymentConfig $deploymentConfig
     * @param Locale $locale
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreView $storeView,
        UserCollectionFactory $userCollectionFactory,
        DeploymentConfig $deploymentConfig,
        Locale $locale,
        LoggerInterface $logger
    ) {
        $this->storeView = $storeView;
        $this->userCollFactory = $userCollectionFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->locale = $locale;
        $this->logger = $logger;
    }

    /**
     * Get locales that are used for a given theme.
     * If it is a frontend theme, return supported frontend languages.
     * If it is an adminhtml theme, return languages that admin users have configured together with deployment config.
     *
     * @param Package $package
     *
     * @return array
     */
    public function getUsedPackageLocales(Package $package): array
    {
        switch ($package->getArea()) {
            case Area::AREA_ADMINHTML:
                $locales = $this->getUsedAdminLocales();
                break;
            case Area::AREA_FRONTEND:
                $locales = $this->getUsedStoreLocales();
                break;
            default:
                $locales = array_merge($this->getUsedAdminLocales(), $this->getUsedStoreLocales());
        }
        return $this->validateLocales($locales);
    }

    /**
     * Get used admin user locales, en_US is always included by default.
     *
     * @return array
     */
    private function getUsedAdminLocales(): array
    {
        if ($this->usedAdminLocales === null) {
            $deploymentConfig = $this->getDeploymentAdminLocales();
            $this->usedAdminLocales = array_merge([AppInterface::DISTRO_LOCALE_CODE], $deploymentConfig);

            if (!$this->isDbConnectionAvailable()) {
                return $this->usedAdminLocales;
            }

            /** @var UserCollection $userCollection */
            $userCollection = $this->userCollFactory->create();
            /** @var UserInterface $adminUser */
            foreach ($userCollection as $adminUser) {
                $this->usedAdminLocales[] = $adminUser->getInterfaceLocale();
            }
        }
        return $this->usedAdminLocales;
    }

    /**
     * Get used store locales.
     *
     * @return array
     */
    private function getUsedStoreLocales(): array
    {
        if ($this->usedStoreLocales === null) {
            $this->usedStoreLocales = $this->isDbConnectionAvailable()
                ? $this->storeView->retrieveLocales()
                : [AppInterface::DISTRO_LOCALE_CODE];
        }
        return $this->usedStoreLocales;
    }

    /**
     * Strip out duplicates and break on invalid locale codes.
     *
     * @param array $usedLocales
     *
     * @return array
     * @throws InvalidArgumentException if unknown locale is provided by the store configuration
     */
    private function validateLocales(array $usedLocales): array
    {
        return array_map(
            function ($locale) {
                if (!$this->locale->isValid($locale)) {
                    throw new InvalidArgumentException(
                        $locale . ' argument has invalid value, run info:language:list for list of available locales'
                    );
                }

                return $locale;
            },
            array_unique($usedLocales)
        );
    }

    /**
     * Check if a database connection is already set up.
     *
     * @return bool
     */
    private function isDbConnectionAvailable(): bool
    {
        try {
            $connections = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS, []);
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception);
        }
        return !empty($connections);
    }

    /**
     * Retrieve deployment configuration for admin locales that have to be deployed.
     *
     * @return array|mixed|string|null
     */
    private function getDeploymentAdminLocales(): array
    {
        try {
            return $this->deploymentConfig
                ->get(self::ADMIN_LOCALES_FOR_DEPLOY, []);
        } catch (LocalizedException $exception) {
            return [];
        }
    }
}
