<?php
/**
 * Factory implementation for the PubSub_FormatterInterface
 *
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
 * @category    Magento
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Formatter;

use Magento\ObjectManager;
use Magento\Outbound\FormatterInterface;

class Factory
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array representing the map for formats and formatter classes
     */
    protected $_formatterMap = array();

    /**
     * @param array $formatterMap
     * @param ObjectManager $objectManager
     */
    public function __construct(
        array $formatterMap,
        ObjectManager $objectManager
    ) {
        $this->_formatterMap = $formatterMap;
        $this->_objectManager = $objectManager;
    }

    /**
     * Get formatter for specified format
     *
     * @param string $format
     * @return FormatterInterface
     * @throws \LogicException
     */
    public function getFormatter($format)
    {
        if (!isset($this->_formatterMap[$format])) {
            throw new \LogicException("There is no formatter for the format given: {$format}");
        }
        $formatterClassName = $this->_formatterMap[$format];

        $formatter =  $this->_objectManager->get($formatterClassName);
        if (!$formatter instanceof FormatterInterface) {
            throw new \LogicException("Formatter class for {$format} does not implement FormatterInterface.");
        }
        return $formatter;
    }

}
