<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Robots\Model\Data;

/**
 * Processes request to robots.txt file and returns robots.txt data as result
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var Data
     */
    private $robotsData;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Data $robotsData
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Data $robotsData
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->robotsData = $robotsData;

        parent::__construct($context);
    }

    /**
     * Generates robots.txt data and returns it as result
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->robotsData->getData());
        return $resultRaw;
    }
}
