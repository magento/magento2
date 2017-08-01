<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Psr\Log\LoggerInterface as Logger;

/**
 * Class AbstractSearchCriteriaBuilder
 * @package Magento\Framework\Data
 * @since 2.0.0
 */
abstract class AbstractSearchCriteriaBuilder
{
    /**
     * @var ObjectFactory
     * @since 2.0.0
     */
    protected $objectFactory;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $resultObjectInterface;

    /**
     * @param Logger $logger
     * @param ObjectFactory $objectFactory,
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract protected function init();

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getResultObjectInterface()
    {
        return $this->resultObjectInterface;
    }

    /**
     * @return SearchResultInterface
     * @since 2.0.0
     */
    public function make()
    {
        return $this->objectFactory->create($this->getResultObjectInterface(), ['queryBuilder' => $this]);
    }
}
