<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Mtf\Util\Generate\Repository;

/**
 * Class Resource
 *
 */
class Resource extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Set fixture entity_type
     *
     * @param array $fixture
     */
    public function setFixture(array $fixture)
    {
        $this->_mainTable = $fixture['entity_type'];
    }

    /**
     * Load an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        // forbid using resource model
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        //
    }
}
