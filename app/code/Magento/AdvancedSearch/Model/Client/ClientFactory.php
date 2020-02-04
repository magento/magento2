<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

use Magento\Framework\ObjectManagerInterface;

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
     * @param ObjectManagerInterface $objectManager
     * @param string $clientClass
     */
    public function __construct(ObjectManagerInterface $objectManager, $clientClass)
    {
        $this->objectManager = $objectManager;
        $this->clientClass = $clientClass;
    }

    /**
     * Return search client
     *
     * @param array $options
     * @return ClientInterface
     */
    public function create(array $options = [])
    {
        return $this->objectManager->create(
            $this->clientClass,
            ['options' => $options]
        );
    }
}
