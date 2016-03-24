<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Repository;

/**
 * Class Resource
 *
 */
class RepositoryResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|void
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
