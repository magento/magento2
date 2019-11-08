<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config backend model for "Use Custom Admin URL" option
 */
namespace Magento\Config\Model\Config\Backend\Admin;

/**
 * Process custom admin url during configuration value save process.
 *
 * @api
 * @since 100.0.2
 */
class Usecustom extends \Magento\Framework\App\Config\Value
{
    /**
     * Writer of configuration storage
     *
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $_configWriter;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_configWriter = $configWriter;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate custom url
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == 1) {
            $customUrlField = $this->getData('groups/url/fields/custom/value');
            $customUrlConfig = $this->_config->getValue('admin/url/custom');
            if (empty($customUrlField) && empty($customUrlConfig)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please specify the admin custom URL.'));
            }
        }

        return $this;
    }

    /**
     * Delete custom admin url from configuration if "Use Custom Admin Url" option disabled
     *
     * @return $this
     */
    public function afterSave()
    {
        $value = $this->getValue();

        if (!$value) {
            $this->_configWriter->delete(
                Custom::XML_PATH_SECURE_BASE_URL,
                Custom::CONFIG_SCOPE,
                Custom::CONFIG_SCOPE_ID
            );
            $this->_configWriter->delete(
                Custom::XML_PATH_UNSECURE_BASE_URL,
                Custom::CONFIG_SCOPE,
                Custom::CONFIG_SCOPE_ID
            );
        }

        return parent::afterSave();
    }
}
