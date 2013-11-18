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
 * @package     Magento_Rating
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rating option model
 *
 * @method \Magento\Rating\Model\Resource\Rating\Option _getResource()
 * @method \Magento\Rating\Model\Resource\Rating\Option getResource()
 * @method int getRatingId()
 * @method \Magento\Rating\Model\Rating\Option setRatingId(int $value)
 * @method string getCode()
 * @method \Magento\Rating\Model\Rating\Option setCode(string $value)
 * @method int getValue()
 * @method \Magento\Rating\Model\Rating\Option setValue(int $value)
 * @method int getPosition()
 * @method \Magento\Rating\Model\Rating\Option setPosition(int $value)
 *
 * @category    Magento
 * @package     Magento_Rating
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rating\Model\Rating;

class Option extends \Magento\Core\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magento\Rating\Model\Resource\Rating\Option');
    }

    public function addVote()
    {
        $this->getResource()->addVote($this);
        return $this;
    }

    public function setId($id)
    {
        $this->setOptionId($id);
        return $this;
    }
}
