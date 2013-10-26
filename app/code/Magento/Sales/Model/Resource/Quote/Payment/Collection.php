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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quote payments collection
 */
namespace Magento\Sales\Model\Resource\Quote\Payment;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Sales\Model\Payment\Method\Converter
     */
    protected $_converter;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Sales\Model\Payment\Method\Converter $converter
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Sales\Model\Payment\Method\Converter $converter,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $resource);
        $this->_converter = $converter;
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Quote\Payment', 'Magento\Sales\Model\Resource\Quote\Payment');
    }

    /**
     * Setquote filter to result
     *
     * @param int $quoteId
     * @return \Magento\Sales\Model\Resource\Quote\Payment\Collection
     */
    public function setQuoteFilter($quoteId)
    {
        return $this->addFieldToFilter('quote_id', $quoteId);
    }

    /**
     * Unserialize additional_information in each item
     *
     * @return \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }

        /** @var \Magento\Sales\Model\Quote\Payment $item */
        foreach ($this->_items as $item) {
            foreach ($item->getData() as $fieldName => $fieldValue) {
                $item->setData($fieldName, $this->_converter->decode($item, $fieldName));
            }
        }

        return parent::_afterLoad();
    }
}

