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
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Tax Rate Titles Renderer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate;

class Title extends \Magento\View\Element\Template
{
    /**
     * @var array
     */
    protected $_titles;

    /**
     * @var string
     */
    protected $_template = 'rate/title.phtml';

    /**
     * @var \Magento\Tax\Model\Calculation\Rate
     */
    protected $_rate;

    /**
     * @var \Magento\Core\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Core\Model\StoreFactory $storeFactory
     * @param \Magento\Tax\Model\Calculation\Rate $rate
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Core\Model\StoreFactory $storeFactory,
        \Magento\Tax\Model\Calculation\Rate $rate,
        array $data = array()
    ) {
        $this->_rate = $rate;
        $this->_storeFactory = $storeFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getTitles()
    {
        if (is_null($this->_titles)) {
            $this->_titles = array();
            $titles = $this->_rate->getTitles();
            foreach ($titles as $title) {
                $this->_titles[$title->getStoreId()] = $title->getValue();
            }
            foreach ($this->getStores() as $store) {
                if (!isset($this->_titles[$store->getId()])) {
                    $this->_titles[$store->getId()] = '';
                }
            }
        }
        return $this->_titles;
    }

    /**
     * @return mixed
     */
    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = $this->_storeFactory->create()
                ->getResourceCollection()
                ->setLoadDefault(false)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }
}
