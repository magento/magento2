<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Plugin;

use Magento\DirectoryGraphQl\Model\Resolver\Currency\Identity;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Directory\Model\Currency as CurrencyModel;

/**
 * Currency plugin
 */
class Currency implements IdentityInterface
{
    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Add graphql store config tag to the store group cache identities.
     *
     * @param CurrencyModel $subject
     * @param CurrencyModel $result
     * @return CurrencyModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveRates(CurrencyModel $subject, CurrencyModel $result): CurrencyModel
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [Identity::CACHE_TAG];
    }
}
