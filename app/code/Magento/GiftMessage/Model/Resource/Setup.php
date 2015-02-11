<?php
/**
 * Gift Message resource setup
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Resource;

use Magento\Framework\Setup\ModuleDataResourceInterface;

class Setup extends \Magento\Setup\Module\DataSetup
{
    /**
     * @var \Magento\Catalog\Model\Resource\SetupFactory
     */
    protected $catalogSetupFactory;

    /**
     * @var \Magento\Quote\Model\Resource\SetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var \Magento\Sales\Model\Resource\SetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @param \Magento\Eav\Model\Entity\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\Catalog\Model\Resource\SetupFactory $catalogSetupFactory
     * @param \Magento\Quote\Model\Resource\SetupFactory $quoteSetupFactory
     * @param \Magento\Sales\Model\Resource\SetupFactory $salesSetupFactory
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Setup\Context $context,
        $resourceName,
        \Magento\Catalog\Model\Resource\SetupFactory $catalogSetupFactory,
        \Magento\Quote\Model\Resource\SetupFactory $quoteSetupFactory,
        \Magento\Sales\Model\Resource\SetupFactory $salesSetupFactory,
        $moduleName = 'Magento_GiftMessage',
        $connectionName = ModuleDataResourceInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->catalogSetupFactory = $catalogSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        parent::__construct(
            $context,
            $resourceName,
            $moduleName,
            $connectionName
        );
    }

    /**
     * Create Quote Setup Factory for GiftMessage
     *
     * @param array $data
     * @return \Magento\Quote\Model\Resource\Setup
     */
    public function createQuoteSetup(array $data = [])
    {
        return $this->quoteSetupFactory->create($data);
    }

    /**
     * Create Sales Setup Factory for GiftMessage
     *
     * @param array $data
     * @return \Magento\Sales\Model\Resource\Setup
     */
    public function createSalesSetup(array $data = [])
    {
        return $this->salesSetupFactory->create($data);
    }

    /**
     * Create Catalog Setup Factory for GiftMessage
     *
     * @param array $data
     * @return \Magento\Catalog\Model\Resource\Setup
     */
    public function createCatalogSetup(array $data = [])
    {
        return $this->catalogSetupFactory->create($data);
    }
}
