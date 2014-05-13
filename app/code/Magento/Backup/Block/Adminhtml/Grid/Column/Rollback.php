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
 * Grid column block that is displayed only if rollback allowed
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Block\Adminhtml\Grid\Column;

class Rollback extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backupHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backup\Helper\Data $backupHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backup\Helper\Data $backupHelper,
        array $data = array()
    ) {
        $this->_backupHelper = $backupHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check permission for rollback
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->_backupHelper->isRollbackAllowed();
    }
}
