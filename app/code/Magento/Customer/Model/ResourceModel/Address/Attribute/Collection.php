<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Customer Address EAV additional attribute resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Customer\Model\ResourceModel\Attribute\Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'customer_address';

    private AddressConfig $addressConfig;

    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null,
        AddressConfig $addressConfig = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $storeManager,
            $connection,
            $resource
        );
        $this->addressConfig ??= ObjectManager::getInstance()->get(AddressConfig::class);
    }

    public function getWebsite()
    {
        if ($this->_website === null) {
            $this->_website = $this->addressConfig->getStore()->getWebsite();
        }
        return $this->_website;
    }
}
