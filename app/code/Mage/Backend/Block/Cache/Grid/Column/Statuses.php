<?php
/**
 * Status column for Cache grid
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Block_Cache_Grid_Column_Statuses extends Mage_Backend_Block_Widget_Grid_Column
{
    /**
     * Invalidated cache types
     *
     * @var array
     */
    protected $_invalidedTypes = array();

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Core_Model_App $app
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_App $app,
        array $data = array()
    ) {
        parent::__construct ($context, $data);

        $this->_app = $app;
    }

    /**
     * Add to column decorated status
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return array($this, 'decorateStatus');
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param  Mage_Core_Model_Abstract $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $this->_invalidatedTypes = $this->_app->getCacheInstance()->getInvalidatedTypes();
        if (isset($this->_invalidatedTypes[$row->getId()])) {
            $cell = '<span class="grid-severity-minor"><span>' . $this->__('Invalidated') . '</span></span>';
        } else {
            if ($row->getStatus()) {
                $cell = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
            } else {
                $cell = '<span class="grid-severity-critical"><span>' . $value . '</span></span>';
            }
        }
        return $cell;
    }
}
