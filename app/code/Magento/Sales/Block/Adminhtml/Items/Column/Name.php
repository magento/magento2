<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Items\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\TruncateFilter\Result;
use Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Sales Order items name column renderer
 *
 * @api
 * @since 100.0.2
 */
class Name extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param array $data
     * @param CatalogHelper|null $catalogHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        array $data = [],
        ?CatalogHelper $catalogHelper = null
    ) {
        $data['catalogHelper'] = $catalogHelper ?? ObjectManager::getInstance()->get(CatalogHelper::class);
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }

    /**
     * @var Result
     */
    private $truncateResult = null;

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        $this->truncateResult = $this->filterManager->truncateFilter(
            $value,
            ['length' => $length, 'etc' => $etc, 'breakWords' => $breakWords]
        );
        return $this->truncateResult->getValue();
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value)
    {
        $remainder = '';
        $this->truncateString($value, 55, '', $remainder);
        $result = [
            'value' => nl2br($this->truncateResult->getValue()),
            'remainder' => nl2br($this->truncateResult->getRemainder())
        ];

        return $result;
    }
}
