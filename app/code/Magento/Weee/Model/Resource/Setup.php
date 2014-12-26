<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Weee\Model\Resource;

/**
 * Setup Model of Sales Module
 */
class Setup extends \Magento\Framework\Module\DataSetup
{
    /**
     * @var \Magento\Sales\Model\Resource\SetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var \Magento\Quote\Model\Resource\SetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @param \Magento\Eav\Model\Entity\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\Sales\Model\Resource\SetupFactory $salesSetupFactory
     * @param \Magento\Quote\Model\Resource\SetupFactory $quoteSetupFactory
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Setup\Context $context,
        $resourceName,
        \Magento\Sales\Model\Resource\SetupFactory $salesSetupFactory,
        \Magento\Quote\Model\Resource\SetupFactory $quoteSetupFactory,
        $moduleName = 'Magento_Weee',
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        parent::__construct(
            $context,
            $resourceName,
            $moduleName,
            $connectionName
        );
    }

    /**
     * Create Sales Setup Factory for Quote
     *
     * @param array $data
     * @return \Magento\Sales\Model\Resource\Setup
     */
    public function createSalesSetup(array $data)
    {
        return $this->salesSetupFactory->create($data);
    }

    /**
     * Create Quote Setup Factory for Quote
     *
     * @param array $data
     * @return \Magento\Quote\Model\Resource\Setup
     */
    public function createQuoteSetup(array $data)
    {
        return $this->quoteSetupFactory->create($data);
    }
}
