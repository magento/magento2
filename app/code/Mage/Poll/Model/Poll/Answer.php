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
 * @category    Mage
 * @package     Mage_Poll
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll answers model
 *
 * @method Mage_Poll_Model_Resource_Poll_Answer _getResource()
 * @method Mage_Poll_Model_Resource_Poll_Answer getResource()
 * @method int getPollId()
 * @method Mage_Poll_Model_Poll_Answer setPollId(int $value)
 * @method string getAnswerTitle()
 * @method Mage_Poll_Model_Poll_Answer setAnswerTitle(string $value)
 * @method int getVotesCount()
 * @method Mage_Poll_Model_Poll_Answer setVotesCount(int $value)
 * @method int getAnswerOrder()
 * @method Mage_Poll_Model_Poll_Answer setAnswerOrder(int $value)
 *
 * @category    Mage
 * @package     Mage_Poll
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Poll_Model_Poll_Answer extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('Mage_Poll_Model_Resource_Poll_Answer');
    }

    public function countPercent($poll)
    {
        $this->setPercent(
            round(($poll->getVotesCount() > 0 ) ? ($this->getVotesCount() * 100 / $poll->getVotesCount()) : 0, 2)
        );
        return $this;
    }

    protected function _afterSave()
    {
        Mage::getModel('Mage_Poll_Model_Poll')
            ->setId($this->getPollId())
            ->resetVotesCount();
    }

    protected function _beforeDelete()
    {
        $this->setPollId($this->load($this->getId())->getPollId());
    }

    protected function _afterDelete()
    {
        Mage::getModel('Mage_Poll_Model_Poll')
            ->setId($this->getPollId())
            ->resetVotesCount();
    }
}
