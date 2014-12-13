<?php
/**
 * Connection adapter factory
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Model\Resource\Type\Db;

use Magento\Framework\ObjectManagerInterface;

class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $connectionConfig)
    {
        if (!$connectionConfig || !isset($connectionConfig['active']) || !$connectionConfig['active']) {
            return null;
        }

        $adapterInstance = $this->objectManager->create(
            'Magento\Framework\App\Resource\ConnectionAdapterInterface',
            ['config' => $connectionConfig]
        );

        return $adapterInstance->getConnection($this->objectManager->get('Magento\Framework\DB\LoggerInterface'));
    }
}
