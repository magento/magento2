<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Backend model for static compilation mode switcher
 * @since 2.2.0
 */
class WorkflowType extends \Magento\Framework\App\Config\Value
{
    /**
     * @var CleanupFiles
     * @since 2.2.0
     */
    private $cleaner;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CleanupFiles $cleaner
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array|null $data
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CleanupFiles $cleaner,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->cleaner = $cleaner;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function beforeSave()
    {
        if ($this->isValueChanged()
            && $this->_appState->getMode() == \Magento\Framework\App\State::MODE_PRODUCTION
            && $this->getValue() == \Magento\Developer\Model\Config\Source\WorkflowType::CLIENT_SIDE_COMPILATION) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Client side compilation doesn\'t work in production mode')
            );
        }
        return parent::beforeSave();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function afterSave()
    {
        parent::afterSave();

        if ($this->isValueChanged()) {
            $this->cleaner->clearMaterializedViewFiles();
        }
        return $this;
    }
}
