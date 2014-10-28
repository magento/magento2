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
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard tab abstract
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractDashboard extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Backend\Helper\Dashboard\AbstractDashboard
     */
    protected $_dataHelper = null;

    /**
     * @var \Magento\Reports\Model\Resource\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\Resource\Order\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Reports\Model\Resource\Order\CollectionFactory $collectionFactory,
        array $data = array()
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array|\Magento\Framework\Model\Resource\Db\Collection\AbstractCollection|\Magento\Eav\Model\Entity\Collection\Abstract
     */
    public function getCollection()
    {
        return $this->getDataHelper()->getCollection();
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->getDataHelper()->getCount();
    }

    /**
     * Get data helper
     *
     * @return \Magento\Backend\Helper\Dashboard\AbstractDashboard
     */
    public function getDataHelper()
    {
        return $this->_dataHelper;
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_prepareData();
        return parent::_prepareLayout();
    }
}
