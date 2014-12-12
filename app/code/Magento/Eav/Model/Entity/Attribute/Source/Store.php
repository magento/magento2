<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Eav\Model\Entity\Attribute\Source;

/**
 * Customer store_id attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
    ) {
        parent::__construct($coreData, $attrOptionCollectionFactory, $attrOptionFactory);
        $this->_storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * Retrieve Full Option values array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->_storeCollectionFactory->create()->load()->toOptionArray();
        }
        return $this->_options;
    }
}
