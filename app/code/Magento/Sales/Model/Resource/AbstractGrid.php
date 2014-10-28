<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\Resource as AppResource;

/**
 * Class AbstractGrid
 */
abstract class AbstractGrid extends AbstractDb implements GridInterface
{
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $gridTableName;

    /**
     * @var string
     */
    protected $orderTableName = 'sales_flat_order';

    /**
     * @var string
     */
    protected $addressTableName = 'sales_flat_order_address';

    /**
     * @param AppResource $resource
     */
    public function __construct(AppResource $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        //
    }

    /**
     * Returns connection
     *
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->_resources->getConnection('write');
        }
        return $this->connection;
    }

    /**
     * Purge grid row
     *
     * @param int|string $value
     * @param null|string $field
     * @return int
     */
    public function purge($value, $field = null)
    {
        return $this->getConnection()->delete(
            $this->getTable($this->gridTableName),
            [($field ?: 'entity_id') . ' = ?' => $value]
        );
    }
}
