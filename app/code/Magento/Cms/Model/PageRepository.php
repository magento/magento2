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
namespace Magento\Cms\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PageRepository
 */
class PageRepository implements \Magento\Cms\Api\PageRepositoryInterface
{
    /**
     * @var \Magento\Cms\Model\Resource\Page
     */
    protected $resource;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Cms\Model\Resource\Page\CollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var \Magento\Framework\DB\QueryBuilderFactory
     */
    protected $queryBuilderFactory;

    /**
     * @var \Magento\Framework\DB\MapperFactory
     */
    protected $mapperFactory;

    /**
     * @param Resource\Page $resource
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\Resource\Page\CollectionFactory $pageCollectionFactory
     * @param \Magento\Framework\DB\QueryBuilderFactory $queryBuilderFactory
     * @param \Magento\Framework\DB\MapperFactory $mapperFactory
     */
    public function __construct(
        \Magento\Cms\Model\Resource\Page $resource,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\Resource\Page\CollectionFactory $pageCollectionFactory,
        \Magento\Framework\DB\QueryBuilderFactory $queryBuilderFactory,
        \Magento\Framework\DB\MapperFactory $mapperFactory
    ) {
        $this->resource = $resource;
        $this->pageFactory = $pageFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * Save Page data
     *
     * @param \Magento\Cms\Api\Data\PageInterface $page
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws CouldNotSaveException
     */
    public function save(\Magento\Cms\Api\Data\PageInterface $page)
    {
        try {
            $this->resource->save($page);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException($exception->getMessage());
        }
        return $page;
    }

    /**
     * Load Page data by given Page Identity
     *
     * @param string $pageId
     * @return \Magento\Cms\Model\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($pageId)
    {
        $page = $this->pageFactory->create();
        $this->resource->load($page, $pageId);
        if (!$page->getId()) {
            throw new NoSuchEntityException(sprintf('CMS Page with id "%s" does not exist.', $pageId));
        }
        return $page;
    }

    /**
     * Load Page data collection by given search criteria
     *
     * @param \Magento\Cms\Api\PageCriteriaInterface $criteria
     * @return \Magento\Cms\Api\Data\PageCollectionInterface
     */
    public function getList(\Magento\Cms\Api\PageCriteriaInterface $criteria)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->setCriteria($criteria);
        $queryBuilder->setResource($this->resource);
        $query = $queryBuilder->create();
        $collection = $this->pageCollectionFactory->create(['query' => $query]);
        return $collection;
    }

    /**
     * Delete Page
     *
     * @param \Magento\Cms\Api\Data\PageInterface $page
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Magento\Cms\Api\Data\PageInterface $page)
    {
        try {
            $this->resource->delete($page);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException($exception->getMessage());
        }
        return true;
    }

    /**
     * Delete Page by given Page Identity
     *
     * @param string $pageId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($pageId)
    {
        return $this->delete($this->get($pageId));
    }
}
