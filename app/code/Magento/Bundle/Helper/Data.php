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
 * @category    Magento
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Helper;

/**
 * Bundle helper
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $_config;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
    ) {
        $this->_config = $config;
        parent::__construct($context);
    }

    /**
     * Retrieve array of allowed product types for bundle selection product
     *
     * @return array
     */
    public function getAllowedSelectionTypes()
    {
        $configData = $this->_config->getType('bundle');
        return isset($configData['allowed_selection_types']) ? $configData['allowed_selection_types'] : array();
    }
}
