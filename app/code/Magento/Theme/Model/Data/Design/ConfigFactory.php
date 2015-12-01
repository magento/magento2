<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Data\Design;

use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigExtension;
use Magento\Theme\Api\Data\DesignConfigInterfaceFactory;
use Magento\Theme\Model\Design\Config\MetadataProviderInterface;
use Magento\Theme\Api\Data\DesignConfigDataInterfaceFactory;
use Magento\Theme\Api\Data\DesignConfigExtensionFactory;
use Magento\Theme\Api\Data\DesignConfigInterface;

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
     * @param DesignConfigInterfaceFactory $designConfigFactory
     * @param MetadataProviderInterface $metadataProvider
     * @param DesignConfigDataInterfaceFactory $designConfigDataFactory
     * @param DesignConfigExtensionFactory $configExtensionFactory
     */
    public function __construct(
        DesignConfigInterfaceFactory $designConfigFactory,
        MetadataProviderInterface $metadataProvider,
        DesignConfigDataInterfaceFactory $designConfigDataFactory,
        DesignConfigExtensionFactory $configExtensionFactory
    ) {
        $this->designConfigFactory = $designConfigFactory;
        $this->metadataProvider = $metadataProvider;
        $this->designConfigDataFactory = $designConfigDataFactory;
        $this->configExtensionFactory = $configExtensionFactory;
    }

    /**
     * @param array $data
     * @return DesignConfigInterface
     */
    public function create(array $data)
    {
        /** @var DesignConfigInterface $designConfigData */
        $designConfigData = $this->designConfigFactory->create();
        $designConfigData->setScope($data['scope']);
        $designConfigData->setScopeId($data['scopeId']);

        $configData = [];
        foreach ($this->metadataProvider->get() as $name => $metadata) {
            /** @var DesignConfigDataInterface $configDataObject */
            $configDataObject = $this->designConfigDataFactory->create();
            $configDataObject->setPath($metadata['path']);
            $configDataObject->setFieldConfig($metadata);
            $configDataObject->setValue($data['params'][$name]);
            $configData[] = $configDataObject;
        }
        /** @var DesignConfigExtension $designConfigExtension */
        $designConfigExtension = $this->configExtensionFactory->create();
        $designConfigExtension->setDesignConfigData($configData);
        $designConfigData->setExtensionAttributes($designConfigExtension);

        return $designConfigData;
    }
}
