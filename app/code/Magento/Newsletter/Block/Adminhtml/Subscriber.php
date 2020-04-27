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

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Newsletter\Model\ResourceModel\Queue\Collection;
use Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory;

/**
 * Newsletter Subscriber block
 *
 * @api
 * @since 100.0.2
 */
class Subscriber extends Template
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
    protected $_template = 'Magento_Newsletter::subscriber/list.phtml';

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Return queue collection with loaded neversent queues
     *
     * @return Collection
     */
    public function getQueueCollection()
    {
        if ($this->_queueCollection === null) {
            /** @var $this->_queueCollection Collection */
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
