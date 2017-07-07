<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter subscriber grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml;

use Magento\Newsletter\Model\ResourceModel\Queue\Collection;

/**
 * @api
 */
class Subscriber extends \Magento\Backend\Block\Template
{
    /**
     * Queue collection
     *
     * @var Collection
     */
    protected $_queueCollection = null;

    /**
     * @var string
     */
    protected $_template = 'subscriber/list.phtml';

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepares block to render
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * Return queue collection with loaded neversent queues
     *
     * @return Collection
     */
    public function getQueueCollection()
    {
        if ($this->_queueCollection === null) {
            /** @var $this->_queueCollection \Magento\Newsletter\Model\ResourceModel\Queue\Collection */
            $this->_queueCollection = $this
                ->_collectionFactory
                ->create()
                ->addTemplateInfo()
                ->addOnlyUnsentFilter()
                ->load();
        }

        return $this->_queueCollection;
    }

    /**
     * Get add option for queue
     *
     * @return mixed
     */
    public function getShowQueueAdd()
    {
        return $this->getChildBlock('grid')->getShowQueueAdd();
    }

    /**
     * Return list of neversent queues for select
     *
     * @return array
     */
    public function getQueueAsOptions()
    {
        return $this->getQueueCollection()->toOptionArray();
    }
}
