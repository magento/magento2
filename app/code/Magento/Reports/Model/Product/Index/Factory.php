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
namespace Magento\Reports\Model\Product\Index;

class Factory
{
    const TYPE_COMPARED = 'compared';

    const TYPE_VIEWED = 'viewed';

    /**
     * @var array
     */
    protected $_typeClasses = array(
        self::TYPE_COMPARED => 'Magento\Reports\Model\Product\Index\Compared',
        self::TYPE_VIEWED => 'Magento\Reports\Model\Product\Index\Viewed'
    );

    /**
     * @var \Magento\Reports\Model\Product\Index\Abstract[]
     */
    protected $_instances;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @return \Magento\Reports\Model\Product\Index\AbstractIndex
     * @throws \InvalidArgumentException
     */
    public function get($type)
    {
        if (!isset($this->_instances[$type])) {
            if (!isset($this->_typeClasses[$type])) {
                throw new \InvalidArgumentException("{$type} is not index model");
            }
            $this->_instances[$type] = $this->_objectManager->create($this->_typeClasses[$type]);
        }
        return $this->_instances[$type];
    }
}
