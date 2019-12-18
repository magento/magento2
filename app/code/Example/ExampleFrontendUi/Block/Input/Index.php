<?php

namespace Example\ExampleFrontendUi\Block\Input;

use Magento\Framework\Exception\NoSuchEntityException as NoSuchEntityExceptionAlias;
use Magento\Framework\View\Element\Template;
use Magento\TestFramework\Event\Magento;

/**
 * Class Example Input Index
 * @package Example\ExampleFrontendUi\Block\Input
 */

class Index extends Template
{
    public function __construct(
        Template\Context $context,
        array $data = [],
        \Magento\Store\Model\StoreManagerInterface $storeManager
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
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

}
