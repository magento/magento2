<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Data\Design;

use Magento\Framework\App\ScopeValidatorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigExtension;
use Magento\Theme\Api\Data\DesignConfigInterfaceFactory;
use Magento\Theme\Model\Design\Config\MetadataProviderInterface;
use Magento\Theme\Api\Data\DesignConfigDataInterfaceFactory;
use Magento\Theme\Api\Data\DesignConfigExtensionFactory;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigFactory
{
    /**
     * @var DesignConfigInterfaceFactory
     */
    protected $designConfigFactory;

    /**
     * @var MetadataProviderInterface
     */
    protected $metadataProvider;

    /**
     * @var DesignConfigDataInterfaceFactory
     */
    protected $designConfigDataFactory;

    /**
     * @var DesignConfigExtensionFactory
     */
    protected $configExtensionFactory;

    /**
     * @var ScopeValidatorInterface
     */
    protected $scopeValidator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param DesignConfigInterfaceFactory $designConfigFactory
     * @param MetadataProviderInterface $metadataProvider
     * @param DesignConfigDataInterfaceFactory $designConfigDataFactory
     * @param DesignConfigExtensionFactory $configExtensionFactory
     * @param ScopeValidatorInterface $scopeValidator
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DesignConfigInterfaceFactory $designConfigFactory,
        MetadataProviderInterface $metadataProvider,
        DesignConfigDataInterfaceFactory $designConfigDataFactory,
        DesignConfigExtensionFactory $configExtensionFactory,
        ScopeValidatorInterface $scopeValidator,
        StoreManagerInterface $storeManager
    ) {
        $this->designConfigFactory = $designConfigFactory;
        $this->metadataProvider = $metadataProvider;
        $this->designConfigDataFactory = $designConfigDataFactory;
        $this->configExtensionFactory = $configExtensionFactory;
        $this->scopeValidator = $scopeValidator;
        $this->storeManager = $storeManager;
    }

    /**
     * Create Design Configuration for scope
     *
     * @param mixed $scope
     * @param int $scopeId
     * @param array $data
     * @return DesignConfigInterface
     * @throws LocalizedException
     */
    public function create($scope, $scopeId, array $data = [])
    {
        if (!$this->scopeValidator->isValidScope($scope, $scopeId)) {
            throw new LocalizedException(__('The scope or scope ID is invalid. Verify both and try again.'));
        }
        $designConfigData = $this->getDesignConfigData($scope, $scopeId);

        $configData = [];
        foreach ($this->metadataProvider->get() as $name => $metadata) {
            $metadata['field'] = $name;
            /** @var DesignConfigDataInterface $configDataObject */
            $configDataObject = $this->designConfigDataFactory->create();
            $configDataObject->setPath($metadata['path']);
            $configDataObject->setFieldConfig($metadata);
            if (isset($data[$name])) {
                $configDataObject->setValue($data[$name]);
            }
            $configData[] = $configDataObject;
        }
        /** @var DesignConfigExtension $designConfigExtension */
        $designConfigExtension = $this->configExtensionFactory->create();
        $designConfigExtension->setDesignConfigData($configData);
        $designConfigData->setExtensionAttributes($designConfigExtension);

        return $designConfigData;
    }

    /**
     * Retrieve design config data with correct scope
     *
     * @param string $scope
     * @param string $scopeId
     * @return DesignConfigInterface
     */
    protected function getDesignConfigData($scope, $scopeId)
    {
        /** @var DesignConfigInterface $designConfigData */
        $designConfigData = $this->designConfigFactory->create();

        $scopeInfo = $this->getCorrectScope($scope, $scopeId);
        $designConfigData->setScope($scopeInfo['scope']);
        $designConfigData->setScopeId($scopeInfo['scopeId']);

        return $designConfigData;
    }

    /**
     * Retrieve correct scope corresponding single store mode configuration
     *
     * @param string $scope
     * @param string $scopeId
     * @return array
     */
    protected function getCorrectScope($scope, $scopeId)
    {
        $isSingleStoreMode = $this->storeManager->isSingleStoreMode();
        if ($isSingleStoreMode) {
            $websites = $this->storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $scope = ScopeInterface::SCOPE_WEBSITES;
            $scopeId = $singleStoreWebsite->getId();
        }
        return [
            'scope' => $scope,
            'scopeId' => $scopeId
        ];
    }
}
