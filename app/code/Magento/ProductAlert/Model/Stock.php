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
 * @package     Magento_ProductAlert
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert for back in stock model
 *
 * @method \Magento\ProductAlert\Model\Resource\Stock _getResource()
 * @method \Magento\ProductAlert\Model\Resource\Stock getResource()
 * @method int getCustomerId()
 * @method \Magento\ProductAlert\Model\Stock setCustomerId(int $value)
 * @method int getProductId()
 * @method \Magento\ProductAlert\Model\Stock setProductId(int $value)
 * @method int getWebsiteId()
 * @method \Magento\ProductAlert\Model\Stock setWebsiteId(int $value)
 * @method string getAddDate()
 * @method \Magento\ProductAlert\Model\Stock setAddDate(string $value)
 * @method string getSendDate()
 * @method \Magento\ProductAlert\Model\Stock setSendDate(string $value)
 * @method int getSendCount()
 * @method \Magento\ProductAlert\Model\Stock setSendCount(int $value)
 * @method int getStatus()
 * @method \Magento\ProductAlert\Model\Stock setStatus(int $value)
 *
 * @category    Magento
 * @package     Magento_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ProductAlert\Model;

class Stock extends \Magento\Core\Model\AbstractModel
{
    /**
     * @var \Magento\ProductAlert\Model\Resource\Stock\Customer\CollectionFactory
     */
    protected $_customerColFactory;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\ProductAlert\Model\Resource\Stock\Customer\CollectionFactory $customerColFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\ProductAlert\Model\Resource\Stock\Customer\CollectionFactory $customerColFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_customerColFactory = $customerColFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Magento\ProductAlert\Model\Resource\Stock');
    }

    public function getCustomerCollection()
    {
        return $this->_customerColFactory->create();
    }

    public function loadByParam()
    {
        if (!is_null($this->getProductId()) && !is_null($this->getCustomerId()) && !is_null($this->getWebsiteId())) {
            $this->getResource()->loadByParam($this);
        }
        return $this;
    }

    public function deleteCustomer($customerId, $websiteId = 0)
    {
        $this->getResource()->deleteCustomer($this, $customerId, $websiteId);
        return $this;
    }
}
