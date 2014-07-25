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
use Magento\UrlRedirect\Model\StorageInterface;
use Magento\UrlRedirect\Service\V1\Data\Converter;
use Magento\UrlRedirect\Service\V1\Data\Filter;

/**
 * Abstract db storage
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @param Converter $converter
     */
    public function __construct(Converter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByFilter(Filter $filter)
    {
        $rows = $this->doFindAllByFilter($filter);

        $urlRewrites = [];
        foreach ($rows as $row) {
            $urlRewrites[] = $this->createUrlRewrite($row);
        }
        return $urlRewrites;
    }

    /**
     * Find all rows by specific filter. Template method
     *
     * @param Filter $filter
     * @return array
     */
    abstract protected function doFindAllByFilter($filter);

    /**
     * {@inheritdoc}
     */
    public function findByFilter(Filter $filter)
    {
        $row = $this->doFindByFilter($filter);

        return $row ? $this->createUrlRewrite($row) : null;
    }

    /**
     * Find row by specific filter. Template method
     *
     * @param Filter $filter
     * @return array
     */
    abstract protected function doFindByFilter($filter);

    /**
     * {@inheritdoc}
     */
    public function addMultiple(array $urls)
    {
        $flatData = [];
        foreach ($urls as $url) {
            $flatData[] = $this->converter->convertObjectToArray($url);
        }
        $this->doAddMultiple($flatData);
    }

    /**
     * Add multiple data to storage. Template method
     *
     * @param array $data
     * @return int
     */
    abstract protected function doAddMultiple($data);

    /**
     * Create url rewrite object
     *
     * @param array $data
     * @return \Magento\UrlRedirect\Service\V1\Data\UrlRewrite
     */
    protected function createUrlRewrite($data)
    {
        return $this->converter->convertArrayToObject($data);
    }
}
