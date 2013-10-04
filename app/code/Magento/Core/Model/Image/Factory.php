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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Image;

class Factory
{
    /**
     * @var \Magento\Core\Model\Image\AdapterFactory
     */
    protected $_adapterFactory;

    /**
     * @param \Magento\Core\Model\Image\AdapterFactory $adapterFactory
     */
    public function __construct(\Magento\Core\Model\Image\AdapterFactory $adapterFactory)
    {
        $this->_adapterFactory = $adapterFactory;
    }

    /**
     * Return \Magento\Image
     *
     * @param string $fileName
     * @param string $adapterType
     * @return \Magento\Image
     */
    public function create($fileName = null, $adapterType = null)
    {
        $adapter = $this->_adapterFactory->create($adapterType);
        return new \Magento\Image($adapter, $fileName);
    }
}
