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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Menu\Item;

class Factory
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\Factory\Helper
     */
    protected $_helperFactory;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\Factory\Helper $helperFactory
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\Factory\Helper $helperFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_helperFactory = $helperFactory;
    }

    /**
     * Create menu item from array
     *
     * @param array $data
     * @return \Magento\Backend\Model\Menu\Item
     */
    public function create(array $data = array())
    {
        $module = 'Magento\Backend\Helper\Data';
        if (isset($data['module'])) {
            $module = $data['module'];
            unset($data['module']);
        }
        $data = array('data' => $data);
        $data['helper'] = $this->_helperFactory->get($module);
        return $this->_objectManager->create('Magento\Backend\Model\Menu\Item', $data);
    }
}
