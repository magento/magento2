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
namespace Magento\Catalog\Model\Resource\Category\Attribute\Source;

/**
 * Catalog category landing page attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     */
    protected $_cmsLayouts;

    /**
     * @param array $cmsLayouts
     */
    public function __construct(array $cmsLayouts = array())
    {
        $this->_cmsLayouts = $cmsLayouts;
    }

    /**
     * Return cms layout update options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            foreach ($this->_cmsLayouts as $layoutName => $layoutConfig) {
                $this->_options[] = array('value' => $layoutName, 'label' => $layoutConfig);
            }
            array_unshift($this->_options, array('value' => '', 'label' => __('No layout updates')));
        }
        return $this->_options;
    }
}
