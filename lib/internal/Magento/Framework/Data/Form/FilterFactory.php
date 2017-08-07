<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form;

use Magento\Framework\Data\Form\Filter\FilterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * Class \Magento\Framework\Data\Form\FilterFactory
 *
 * @since 2.1.0
 */
class FilterFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create filter instance
     *
     * @param string $filterCode
     * @param array $data
     * @return FilterInterface
     * @since 2.1.0
     */
    public function create($filterCode, array $data = [])
    {
        $filterClass = 'Magento\\Framework\\Data\\Form\\Filter\\' . ucfirst($filterCode);

        $filter = $this->objectManager->create($filterClass, $data);

        if (!$filter instanceof FilterInterface) {
            throw new \InvalidArgumentException(sprintf(
                '%s class must implement %s',
                $filterClass,
                \Magento\Framework\Data\Form\Filter\FilterInterface::class
            ));
        }

        return $filter;
    }
}
