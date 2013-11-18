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
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Block;

class Agreements extends \Magento\Core\Block\Template
{
    /**
     * @var \Magento\Checkout\Model\Resource\Agreement\CollectionFactory
     */
    protected $_agreementCollFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Resource\Agreement\CollectionFactory $agreementCollFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Resource\Agreement\CollectionFactory $agreementCollFactory,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_agreementCollFactory = $agreementCollFactory;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * @return mixed
     */
    public function getAgreements()
    {
        if (!$this->hasAgreements()) {
            if (!$this->_storeConfig->getConfigFlag('checkout/options/enable_agreements')) {
                $agreements = array();
            } else {
                /** @var \Magento\Checkout\Model\Resource\Agreement\Collection $agreements */
                $agreements = $this->_agreementCollFactory->create()
                    ->addStoreFilter($this->_storeManager->getStore()->getId())
                    ->addFieldToFilter('is_active', 1);
            }
            $this->setAgreements($agreements);
        }
        return $this->getData('agreements');
    }
}
