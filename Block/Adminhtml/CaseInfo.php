<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\Guarantee\CreateGuaranteeAbility;

/**
 * Get Signifyd Case Info
 */
class CaseInfo extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CaseInterface
     */
    private $caseEntity = null;

    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @var CreateGuaranteeAbility
     */
    private $createGuaranteeAbility;

    /**
     * @var int
     */
    private static $scoreAccept = 500;

    /**
     * @var int
     */
    private static $scoreDecline = 300;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param CaseManagement $caseManagement
     * @param CreateGuaranteeAbility $createGuaranteeAbility
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CaseManagement $caseManagement,
        CreateGuaranteeAbility $createGuaranteeAbility,
        array $data = []
    ) {
        $this->config = $config;
        $this->caseManagement = $caseManagement;
        $this->createGuaranteeAbility = $createGuaranteeAbility;

        parent::__construct($context, $data);
    }

    /**
     * Checks if service is enabled.
     *
     * @return boolean
     */
    public function isServiceActive()
    {
        return $this->config->isActive();
    }

    /**
     * Gets case entity associated with order id.
     *
     * @return CaseInterface|null
     */
    private function getCaseEntity()
    {
        if (is_null($this->caseEntity)) {
            $this->caseEntity = $this->caseManagement->getByOrderId(
                $this->getOrderId()
            );
        }

        return $this->caseEntity;
    }

    /**
     * Checks if case is exists for order
     *
     * @return bool
     */
    public function isEmptyCase()
    {
        return is_null($this->getCaseEntity());
    }

    /**
     * Gets case status
     *
     * @return string
     */
    public function getCaseStatus()
    {
        return $this->isEmptyCase() ? '' : $this->getCaseEntity()->getStatus();
    }

    /**
     * Gets case score value
     *
     * @return int
     */
    public function getCaseScore()
    {
        return $this->isEmptyCase() ? 0 : $this->getCaseEntity()->getScore();
    }

    /**
     * Gets state of case guarantee eligible.
     *
     * @return string|\Magento\Framework\Phrase
     */
    public function getCaseGuaranteeEligible()
    {
        if ($this->isEmptyCase()) {
            return '';
        }

        return $this->getCaseEntity()->isGuaranteeEligible() ? __('Yes') : __('No');
    }

    /**
     * Gets case guarantee disposition status.
     *
     * @return string
     */
    public function getCaseGuaranteeDisposition()
    {
        if ($this->isEmptyCase()) {
            return '';
        }

        return $this->getCaseEntity()->getGuaranteeDisposition();
    }

    /**
     * Gets case review disposition status.
     *
     * @return string
     */
    public function getCaseReviewDisposition()
    {
        if ($this->isEmptyCase()) {
            return '';
        }

        return $this->getCaseEntity()->getReviewDisposition();
    }

    /**
     * Gets case create date.
     *
     * @return string
     */
    public function getCaseCreatedAt()
    {
        if ($this->isEmptyCase()) {
            return '';
        }

        return $this->getCaseEntity()->getCreatedAt();
    }

    /**
     * Gets case update date.
     *
     * @return string
     */
    public function getCaseUpdatedAt()
    {
        if ($this->isEmptyCase()) {
            return '';
        }

        return $this->getCaseEntity()->getUpdatedAt();
    }

    /**
     * Gets case associated team name.
     *
     * @return string
     */
    public function getCaseAssociatedTeam()
    {
        if ($this->isEmptyCase()) {
            return '';
        }

        $result = 'unknown';
        $team = $this->getCaseEntity()->getAssociatedTeam();
        if (isset($team['teamName'])) {
            $result = $team['teamName'];
        }

        return $result;
    }

    /**
     * Returns cell class name according to case score value.
     * It could be used by merchant to customize order view template.
     *
     * @return string
     */
    public function getScoreClass()
    {
        if ($this->isEmptyCase()) {
            return '';
        }

        $score = $this->getCaseEntity()->getScore();

        if (self::$scoreAccept <= $score) {
            $result = 'green';
        } elseif ($score <= self::$scoreDecline) {
            $result = 'red';
        } else {
            $result = 'yellow';
        }

        return $result;
    }

    /**
     * Gets configuration of allowed buttons.
     *
     * @return array
     */
    public function getButtons()
    {
        $buttons = [];

        if ($this->createGuaranteeAbility->isAvailable($this->getOrderId())) {
            $buttons[] = $this->getSubmitButton();
        }

        return $buttons;
    }

    /**
     * Returns configuration for submit Guarantee request button.
     *
     * @return array
     */
    private function getSubmitButton()
    {
        return [
            'title' => __('Submit Guarantee request'),
            'url' => $this->getUrl('signifyd/guarantee/create'),
            'componentName' => 'submit_guarantee_request',
            'orderId' => $this->getOrderId()
        ];
    }

    /**
     * Retrieves current order Id.
     *
     * @return integer
     */
    private function getOrderId()
    {
        return (int) $this->getRequest()->getParam('order_id');
    }
}
