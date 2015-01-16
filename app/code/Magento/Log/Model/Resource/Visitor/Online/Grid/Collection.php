<?php
/**
 * Log Online visitors collection
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource\Visitor\Online\Grid;

class Collection extends \Magento\Log\Model\Resource\Visitor\Online\Collection
{
    /**
     * @var \Magento\Log\Model\Visitor\OnlineFactory
     */
    protected $_onlineFactory;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Helper\Data $eavHelper
     * @param \Magento\Log\Model\Visitor\OnlineFactory $onlineFactory
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Helper\Data $eavHelper,
        \Magento\Log\Model\Visitor\OnlineFactory $onlineFactory,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_onlineFactory = $onlineFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavHelper,
            $connection,
            $resource
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_onlineFactory->create()->prepare();
        $this->addCustomerData();
        return $this;
    }
}
