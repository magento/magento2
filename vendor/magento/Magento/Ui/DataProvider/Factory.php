<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 */
class Factory
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
     * Create data provider
     *
     * @param string $providerClass
     * @param array $arguments
     * @return mixed
     */
    public function create($providerClass, array $arguments = [])
    {
        return $this->objectManager->create($providerClass, ['arguments' => $arguments]);
    }
}
