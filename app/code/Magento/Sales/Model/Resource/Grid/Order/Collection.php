<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Grid\Order;
use Magento\Framework\App\RequestInterface;

/**
 * Class Collection
 * Collection for order related documents to display grids on order view page
 */
class Collection extends \Magento\Sales\Model\Resource\Grid\Collection
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'order_id';

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param RequestInterface $request
     * @param null|\Zend_Db_Adapter_Abstract $mainTable
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param string|null $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        RequestInterface $request,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Sales\Model\Resource\Grid\Document',
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->request = $request;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model,
            $connection,
            $resource
        );
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @throws \Exception
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $order = $this->request->getParam('current_order');
        if ($order) {
            $this->addFieldToFilter($this->_orderField, $order->getId());
        }
        return $this;
    }
}
