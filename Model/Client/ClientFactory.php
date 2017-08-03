<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\AdvancedSearch\Model\Client\ClientFactory
 *
 * @since 2.1.0
 */
class ClientFactory implements ClientFactoryInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * @var string
     * @since 2.1.0
     */
    private $clientClass;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $clientClass
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function create(array $options = [])
    {
        return $this->objectManager->create(
            $this->clientClass,
            ['options' => $options]
        );
    }
}
