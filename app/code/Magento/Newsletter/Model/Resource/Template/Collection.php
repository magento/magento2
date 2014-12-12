<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Newsletter\Model\Resource\Template;

/**
 * Newsletter templates collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Define resource model and model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Newsletter\Model\Template', 'Magento\Newsletter\Model\Resource\Template');
    }

    /**
     * Load only actual template
     *
     * @return $this
     */
    public function useOnlyActual()
    {
        $this->addFieldToFilter('template_actual', 1);

        return $this;
    }

    /**
     * Returns options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('template_id', 'template_code');
    }
}
