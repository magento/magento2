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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll vote controller
 *
 * @file        Vote.php
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Poll_VoteController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Add Vote to Poll
     *
     * @return void
     */
    public function addAction()
    {
        $pollId     = intval($this->getRequest()->getParam('poll_id'));
        $answerId   = intval($this->getRequest()->getParam('vote'));

        /** @var $poll Mage_Poll_Model_Poll */
        $poll = Mage::getModel('Mage_Poll_Model_Poll')->load($pollId);

        /**
         * Check poll data
         */
        if ($poll->getId() && !$poll->getClosed() && !$poll->isVoted()) {
            $vote = Mage::getModel('Mage_Poll_Model_Poll_Vote')
                ->setPollAnswerId($answerId)
                ->setIpAddress(Mage::helper('Mage_Core_Helper_Http')->getRemoteAddr(true))
                ->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId());

            $poll->addVote($vote);
            Mage::getSingleton('Mage_Core_Model_Session')->setJustVotedPoll($pollId);
            Mage::dispatchEvent(
                'poll_vote_add',
                array(
                    'poll'  => $poll,
                    'vote'  => $vote
                )
            );
        }
        $this->_redirectReferer();
    }
}
