<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component\Filter;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class FilterPool
 */
class FilterPool
{
    /**
     * Filter types
     *
     * @var array
     */
    protected $filterTypes = [];

    /**
     * Filters poll
     *
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $filters
     */
    public function __construct(ObjectManagerInterface $objectManager, array $filters = [])
    {
        $this->objectManager = $objectManager;
        $this->filterTypes = $filters;
    }

    /**
     * Get filter by type
     *
     * @param string $filterName
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function getFilter($filterName)
    {
        if (!isset($this->filters[$filterName])) {
            if (!isset($this->filterTypes[$filterName])) {
                throw new \InvalidArgumentException(sprintf('Unknown filter type "%s"', $filterName));
            }
            $this->filters[$filterName] = $this->objectManager->create($this->filterTypes[$filterName]);
        }

        return $this->filters[$filterName];
    }
}
