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
namespace Magento\RecurringPayment\Model\Plugin;

/**
 * Plugin for product attribute tabs
 */
class Tabs
{
    /** @var \Magento\Framework\Module\Manager  */
    protected $_moduleManager;

    /**
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(\Magento\Framework\Module\Manager $moduleManager)
    {
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs $subject
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection $result
     *
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetGroupCollection(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs $subject, $result)
    {
        if (!$this->_moduleManager->isOutputEnabled('Magento_RecurringPayment')) {
            foreach ($result as $key => $group) {
                if ($group->getAttributeGroupCode() === 'recurring-payment') {
                    $result->removeItemByKey($key);
                }
            }
        }
        return $result;
    }
}
