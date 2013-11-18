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
namespace Magento\Sales\Block\Order;

class Comments extends \Magento\Core\Block\Template
{
    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Sales\Model\ResourceFactory $resourceFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Sales\Model\ResourceFactory $resourceFactory,
        array $data = array()
    ) {
        $this->_resourceFactory = $resourceFactory;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Current entity (model instance) with getCommentsCollection() method
     *
     * @var \Magento\Sales\Model\AbstractModel
     */
    protected $_entity;

    /**
     * Currect comments collection
     *
     * @var \Magento\Sales\Model\Resource\Order\Comment\Collection\AbstractCollection
     */
    protected $_commentCollection;

    /**
     * Sets comments parent model instance
     *
     * @param \Magento\Sales\Model\AbstractModel
     * @return \Magento\Sales\Block\Order\Comments
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        $this->_commentCollection = null; // Changing model and resource model can lead to change of comment collection
        return $this;
    }

    /**
     * Gets comments parent model instance
     *
     * @return \Magento\Sales\Model\AbstractModel
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Initialize model comments and return comment collection
     *
     * @return \Magento\Sales\Model\Resource\Order\Comment\Collection\AbstractCollection
     * @throws \Magento\Core\Exception
     */
    public function getComments()
    {
        if (is_null($this->_commentCollection)) {
            $entity = $this->getEntity();
            if ($entity instanceof \Magento\Sales\Model\Order\Invoice) {
                $collectionClass = 'Magento\Sales\Model\Resource\Order\Invoice\Comment\Collection';
            } else if ($entity instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $collectionClass = 'Magento\Sales\Model\Resource\Order\Creditmemo\Comment\Collection';
            } else if ($entity instanceof \Magento\Sales\Model\Order\Shipment) {
                $collectionClass = 'Magento\Sales\Model\Resource\Order\Shipment\Comment\Collection';
            } else {
                throw new \Magento\Core\Exception(__('We found an invalid entity model.'));
            }

            $this->_commentCollection = $this->_resourceFactory->create($collectionClass);
            $this->_commentCollection->setParentFilter($entity)
               ->setCreatedAtOrder()
               ->addVisibleOnFrontFilter();
        }

        return $this->_commentCollection;
    }

    /**
     * Returns whether there are comments to show on frontend
     *
     * @return bool
     */
    public function hasComments()
    {
        return $this->getComments()->count() > 0;
    }
}
