<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

use Magento\Framework\ObjectManagerInterface;
use Magento\AdvancedSearch\Helper\Data;

class ClientFactory implements ClientFactoryInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    private $clientClass;

    /**
     * @var string
     */
    private $openSearch;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param $clientClass
     * @param Data $helper
     * @param $openSearch
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $clientClass,
        Data $helper,
        $openSearch = null
    ) {
        $this->objectManager = $objectManager;
        $this->clientClass = $clientClass;
        $this->openSearch = $openSearch;
        $this->helper = $helper;
    }

    /**
     * Return search client
     *
     * @param array $options
     * @return ClientInterface
     */
    public function create(array $options = [])
    {
        $class = $this->clientClass;
        if ($this->helper->isClientOpenSearchV2()) {
            $class = $this->openSearch;
        }

        return $this->objectManager->create(
            $class,
            ['options' => $options]
        );
    }
}
