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

/**
 * Abstract Tax Class
 */
namespace Magento\Tax\Model\TaxClass;

abstract class AbstractType extends \Magento\Framework\Object implements \Magento\Tax\Model\TaxClass\Type\TypeInterface
{
    /**
     * @var \Magento\Tax\Model\Calculation\Rule
     */
    protected $_calculationRule;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param array $data
     */
    public function __construct(\Magento\Tax\Model\Calculation\Rule $calculationRule, array $data = array())
    {
        parent::__construct($data);
        $this->_calculationRule = $calculationRule;
    }

    /**
     * Get Collection of Tax Rules that are assigned to this tax class
     *
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    public function getAssignedToRules()
    {
        return $this->_calculationRule->getCollection()->setClassTypeFilter($this->_classType, $this->getId());
    }
}
