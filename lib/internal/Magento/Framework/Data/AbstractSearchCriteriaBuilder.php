<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Psr\Log\LoggerInterface as Logger;

/**
 * Class AbstractSearchCriteriaBuilder
 * @package Magento\Framework\Data
 */
abstract class AbstractSearchCriteriaBuilder
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var string
     */
    protected $resultObjectInterface;

    /**
     * @param Logger $logger
     * @param ObjectFactory $objectFactory,
     */
    public function __construct(
        Logger $logger,
        ObjectFactory $objectFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->logger = $logger;
        $this->init();
    }

    /**
     * @return string
     */
    abstract protected function init();

    /**
     * @return string
     */
    protected function getResultObjectInterface()
    {
        return $this->resultObjectInterface;
    }

    /**
     * @return SearchResultInterface
     */
    public function make()
    {
        return $this->objectFactory->create($this->getResultObjectInterface(), ['queryBuilder' => $this]);
    }
}
