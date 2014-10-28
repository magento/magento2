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
namespace Magento\Ui\Filter;

use Magento\Framework\ObjectManager;

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
    protected $filterTypes = [
        'filter_input' => 'Magento\Ui\Filter\Type\Input',
        'filter_select' => 'Magento\Ui\Filter\Type\Select',
        'filter_range' => 'Magento\Ui\Filter\Type\Range',
        'filter_date' => 'Magento\Ui\Filter\Type\Date',
        'filter_store' => 'Magento\Ui\Filter\Type\Store'
    ];

    /**
     * Filters poll
     *
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get filter by type
     *
     * @param string $dataType
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function getFilter($dataType)
    {
        if (!isset($this->filters[$dataType])) {
            if (!isset($this->filterTypes[$dataType])) {
                throw new \InvalidArgumentException(sprintf('Unknown filter type "%s"', $dataType));
            }
            $this->filters[$dataType] = $this->objectManager->create($this->filterTypes[$dataType]);
        }

        return $this->filters[$dataType];
    }
}
