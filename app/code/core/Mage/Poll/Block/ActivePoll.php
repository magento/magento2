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
 * Poll block
 *
 * @file        Poll.php
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Poll_Block_ActivePoll extends Mage_Core_Block_Template
{
    /**
     * Poll templates
     *
     * @var array
     */
    protected $_templates;

    /**
     * Current Poll Id
     *
     * @var int
     */
    protected $_pollId = null;

    /**
     * Already voted by current visitor Poll Ids array
     *
     * @var array|null
     */
    protected $_votedIds = null;

    /**
     * Poll model
     *
     * @var Mage_Poll_Model_Poll
     */
    protected $_pollModel;

    protected function _construct()
    {
        parent::_construct();
        $this->_pollModel = Mage::getModel('Mage_Poll_Model_Poll');
    }

    /**
     * Set current Poll Id
     *
     * @param int $pollId
     * @return Mage_Poll_Block_ActivePoll
     */
    public function setPollId($pollId)
    {
        $this->_pollId = $pollId;
        return $this;
    }

    /**
     * Get current Poll Id
     *
     * @return int|null
     */
    public function getPollId()
    {
        return $this->_pollId;
    }

    /**
     * Retrieve already voted Poll Ids
     *
     * @return array|null
     */
    public function getVotedPollsIds()
    {
        if ($this->_votedIds === null) {
            $this->_votedIds = $this->_pollModel->getVotedPollsIds();
        }
        return $this->_votedIds;
    }

    /**
     * Get Ids of all active Polls
     *
     * @return array
     */
    public function getActivePollsIds()
    {
        return $this->_pollModel
            ->setExcludeFilter($this->getVotedPollsIds())
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->getAllIds();
    }

    /**
     * Get Poll Id to show
     *
     * @return int
     */
    public function getPollToShow()
    {
        if ($this->getPollId()) {
            return $this->getPollId();
        }
        // get last voted poll (from session only)
        $pollId = Mage::getSingleton('Mage_Core_Model_Session')->getJustVotedPoll();
        if (empty($pollId)) {
            // get random not voted yet poll
            $votedIds = $this->getVotedPollsIds();
            $pollId = $this->_pollModel
                ->setExcludeFilter($votedIds)
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->getRandomId();
        }
        $this->setPollId($pollId);

        return $pollId;
    }

    /**
     * Get Poll related data
     *
     * @param int $pollId
     * @return array|bool
     */
    public function getPollData($pollId)
    {
        if (empty($pollId)) {
            return false;
        }
        $poll = $this->_pollModel->load($pollId);

        $pollAnswers = Mage::getModel('Mage_Poll_Model_Poll_Answer')
            ->getResourceCollection()
            ->addPollFilter($pollId)
            ->load()
            ->countPercent($poll);

        // correct rounded percents to be always equal 100
        $percentsSorted = array();
        $answersArr = array();
        foreach ($pollAnswers as $key => $answer) {
            $percentsSorted[$key] = $answer->getPercent();
            $answersArr[$key] = $answer;
        }
        asort($percentsSorted);
        $total = 0;
        foreach ($percentsSorted as $key => $value) {
            $total += $value;
        }
        // change the max value only
        if ($total > 0 && $total !== 100) {
            $answersArr[$key]->setPercent($value + 100 - $total);
        }

        return array(
            'poll' => $poll,
            'poll_answers' => $pollAnswers,
            'action' => Mage::getUrl('poll/vote/add', array('poll_id' => $pollId, '_secure' => true))
        );
    }


    /**
     * Add poll template
     *
     * @param string $template
     * @param string $type
     * @return Mage_Poll_Block_ActivePoll
     */
    public function setPollTemplate($template, $type)
    {
        $this->_templates[$type] = $template;
        return $this;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $coreSessionModel Mage_Core_Model_Session */
        $coreSessionModel = Mage::getSingleton('Mage_Core_Model_Session');
        $justVotedPollId = $coreSessionModel->getJustVotedPoll();
        if ($justVotedPollId && !$this->_pollModel->isVoted($justVotedPollId)) {
            $this->_pollModel->setVoted($justVotedPollId);
        }

        $pollId = $this->getPollToShow();
        $data = $this->getPollData($pollId);
        $this->assign($data);

        $coreSessionModel->setJustVotedPoll(false);

        if ($this->_pollModel->isVoted($pollId) === true || $justVotedPollId) {
            $this->setTemplate($this->_templates['results']);
        } else {
            $this->setTemplate($this->_templates['poll']);
        }
        return parent::_toHtml();
    }


    /**
     * Get cache key informative items that must be preserved in cache placeholders
     * for block to be rerendered by placeholder
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $items = array(
            'templates' => serialize($this->_templates)
        );

        $items = parent::getCacheKeyInfo() + $items;

        return $items;
    }

}
