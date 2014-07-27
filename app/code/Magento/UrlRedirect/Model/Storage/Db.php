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
namespace Magento\UrlRedirect\Model\Storage;

use Magento\Framework\App\Resource;
use Magento\UrlRedirect\Service\V1\Data\Converter;
use Magento\UrlRedirect\Service\V1\Data\Filter;

/**
 * Db storage
 */
class Db extends AbstractStorage
{
    /**
     * DB Storage table name
     */
    const TABLE_NAME = 'url_rewrite';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @param Converter $converter
     * @param Resource $resource
     */
    public function __construct(Converter $converter, Resource $resource)
    {
        $this->connection = $resource->getConnection(Resource::DEFAULT_WRITE_RESOURCE);

        parent::__construct($converter);
    }

    /**
     * Prepare select statement for specific filter
     *
     * @param Filter $filter
     * @return \Magento\Framework\DB\Select
     */
    protected function prepareSelect($filter)
    {
        $select = $this->connection->select();
        $select->from(self::TABLE_NAME);

        foreach ($filter->getFilter() as $column => $value) {
            $select->where($this->connection->quoteIdentifier($column) . ' IN (?)', $value);
        }
        return $select;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFindAllByFilter($filter)
    {
        return $this->connection->fetchAll($this->prepareSelect($filter));
    }

    /**
     * {@inheritdoc}
     */
    protected function doFindByFilter($filter)
    {
        return $this->connection->fetchRow($this->prepareSelect($filter));
    }

    /**
     * {@inheritdoc}
     */
    protected function doAddMultiple($data)
    {
        $this->connection->insertMultiple(self::TABLE_NAME, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByFilter(Filter $filter)
    {
        $this->connection->query($this->prepareSelect($filter)->deleteFromSelect(self::TABLE_NAME));
    }
}
