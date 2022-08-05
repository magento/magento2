<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Implementation of the notification about MySQL search being removed.
 *
 * @see \Magento\ElasticSearch
 */
class MySQLSearchRemovalNotification implements DataPatchInterface
{
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param NotifierInterface $notifier
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        NotifierInterface $notifier,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->notifier = $notifier;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply(): DataPatchInterface
    {
        if ($this->scopeConfig->getValue('catalog/search/engine') === 'mysql') {
            $message = <<<MESSAGE
Catalog Search is currently configured to use the MySQL engine, which has been deprecated and removed.
Migrate to an Elasticsearch engine to ensure there are no service interruptions.
MESSAGE;

            $this->notifier->addNotice(__('Disable Notice'), __($message));
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
