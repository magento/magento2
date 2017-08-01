<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 2.0.0
 */
class Website implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [];
            foreach ($this->_storeManager->getWebsites() as $website) {
                $id = $website->getId();
                $name = $website->getName();
                if ($id != 0) {
                    $this->_options[] = ['value' => $id, 'label' => $name];
                }
            }
        }
        return $this->_options;
    }
}
