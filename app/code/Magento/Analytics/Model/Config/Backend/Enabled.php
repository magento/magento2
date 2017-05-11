<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Config value backend model.
 */
class Enabled extends Value
{
    /**
     * Path to field subscription enabled into config structure.
     */
    const XML_ENABLED_CONFIG_STRUCTURE_PATH = 'analytics/general/enabled';

    /**
     * Service for processing of activation/deactivation MBI subscription.
     *
     * @var SubscriptionHandler
     */
    private $subscriptionHandler;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param SubscriptionHandler $subscriptionHandler
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        SubscriptionHandler $subscriptionHandler,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->subscriptionHandler = $subscriptionHandler;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Add additional handling after config value was saved.
     *
     * @return Value
     * @throws LocalizedException
     */
    public function afterSave()
    {
        try {
            if ($this->isValueChanged()) {
                $enabled = $this->getData('value');

                if ($enabled) {
                    $this->subscriptionHandler->processEnabled();
                } else {
                    $this->subscriptionHandler->processDisabled();
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            throw new LocalizedException(__('There was an error save new configuration value.'));
        }

        return parent::afterSave();
    }
}
