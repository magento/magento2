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
 * @package     Magento_Usa
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Usa\Model\Resource;


class Setup extends \Magento\Core\Model\Resource\Setup
{
    /**
     * Locale model
     *
     * @var \Magento\Core\Model\Locale
     */
    protected $_localeModel;

    /**
     * @param \Magento\Core\Model\Resource\Setup\Context $context
     * @param string $resourceName
     * @param string $moduleName
     * @param \Magento\Core\Model\Locale $localeModel
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Core\Model\Resource\Setup\Context $context,
        $resourceName,
        $moduleName,
        \Magento\Core\Model\Locale $localeModel,
        $connectionName = ''
    ) {
        $this->_localeModel = $localeModel;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * Get locale
     *
     * @return \Magento\Core\Model\Locale
     */
    public function getLocale()
    {
        return $this->_localeModel;
    }
}