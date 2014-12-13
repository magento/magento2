<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data;

use Magento\Framework\Logger;

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
