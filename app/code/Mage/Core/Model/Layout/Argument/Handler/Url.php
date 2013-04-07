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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout argument. Type url
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Layout_Argument_Handler_Url extends Mage_Core_Model_Layout_Argument_HandlerAbstract
{
    /**
     * @var Mage_Core_Model_UrlInterface
     */
    protected $_urlModel;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_UrlInterface $urlModel
     */
    public function __construct(Magento_ObjectManager $objectManager, Mage_Core_Model_UrlInterface $urlModel)
    {
        parent::__construct($objectManager);

        $this->_urlModel = $urlModel;
    }

    /**
     * Generate url
     * @param string $value
     * @throws InvalidArgumentException
     * @return Mage_Core_Model_Abstract|boolean
     */
    public function process($value)
    {
        if (false === is_array($value) || (!isset($value['path']))) {
            throw new InvalidArgumentException('Passed value has incorrect format');
        }

        $params = array_key_exists('params', $value) ? $value['params'] : null;
        return $this->_urlModel->getUrl($value['path'], $params);
    }
}
