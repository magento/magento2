<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Product type price factory
 */
namespace Magento\Catalog\Model\Product\Type\Price;

class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create price model for product of particular type
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Catalog\Model\Product\Type\Price
     * @throws \Magento\Framework\Model\Exception
     */
    public function create($className, array $data = [])
    {
        $price = $this->_objectManager->create($className, $data);

        if (!$price instanceof \Magento\Catalog\Model\Product\Type\Price) {
            throw new \Magento\Framework\Model\Exception(
                $className . ' doesn\'t extends \Magento\Catalog\Model\Product\Type\Price'
            );
        }
        return $price;
    }
}
