<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Msrp\Block\Adminhtml\Product\Helper\Form;

/**
 * Product form MSRP field helper
 */
class Type extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price
{
    /** @var \Magento\Msrp\Model\Config*/
    protected $config;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Msrp\Model\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Msrp\Model\Config $config,
        array $data = array()
    ) {
        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $storeManager,
            $localeCurrency,
            $taxData,
            $data
        );
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if ($this->config->isEnabled()) {
            return parent::toHtml();
        }
        return '';
    }
}
