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
namespace Magento\Eav\Model\Entity\Attribute\Source;

/**
 * Entity/Attribute/Model - attribute selection source from configuration
 *
 * this class should be abstract, but kept usual for legacy purposes
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Config extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     */
    protected $_optionsData;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->_optionsData = $options;
    }

    /**
     * Retrieve all options for the source from configuration
     *
     * @throws \Magento\Eav\Exception
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = array();

            if (empty($this->_optionsData)) {
                throw new \Magento\Eav\Exception(__('No options found.'));
            }
            foreach ($this->_optionsData as $option) {
                $this->_options[] = array('value' => $option['value'], 'label' => __($option['label']));
            }
        }

        return $this->_options;
    }
}
