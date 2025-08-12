<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config image field backend model for Zend PDF generator
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend\Image;

/**
 * @api
 * @since 100.0.2
 */
class Adapter extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->_imageFactory = $imageFactory;
    }

    /**
     * Checks if chosen image adapter available
     *
     * @throws \Magento\Framework\Exception\LocalizedException If some of adapter dependencies was not loaded
     * @return \Magento\Config\Model\Config\Backend\File
     */
    public function beforeSave()
    {
        try {
            $this->_imageFactory->create($this->getValue());
        } catch (\Exception $e) {
            $message = __('The specified image adapter cannot be used because of: %1', $e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException($message);
        }

        return $this;
    }
}
