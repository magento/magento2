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
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\App\Resource;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;

class Wildcard implements FilterInterface
{

    const CONDITION_LIKE = 'LIKE';
    const CONDITION_NOT_LIKE = 'NOT LIKE';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @param ConditionManager $conditionManager
     */
    public function __construct(
        ConditionManager $conditionManager
    ) {
        $this->conditionManager = $conditionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        $isNegation
    ) {
        /** @var \Magento\Framework\Search\Request\Filter\Wildcard $filter */

        $searchValue = '%' . $filter->getValue() . '%';
        return $this->conditionManager->generateCondition(
            $filter->getField(),
            ($isNegation ? self::CONDITION_NOT_LIKE : self::CONDITION_LIKE),
            $searchValue
        );
    }
}
