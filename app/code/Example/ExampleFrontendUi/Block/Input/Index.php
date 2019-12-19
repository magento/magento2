<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExampleFrontendUi\Block\Input;

use Magento\Framework\Exception\NoSuchEntityException as NoSuchEntityExceptionAlias;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Example Input Index
 * @package Example\ExampleFrontendUi\Block\Input
 */

class Index extends Template
{
    public function __construct(
        Template\Context $context,
        array $data = [],
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * Get store base Url
     *
     * @return string
     * @throws NoSuchEntityExceptionAlias
     */
    public function getBaseUrl(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get message input
     *
     * @return string
     */
    public function getMessageData(): string
    {
        return $this->getMessage();
    }
}
