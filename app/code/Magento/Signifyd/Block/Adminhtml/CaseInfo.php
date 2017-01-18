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
use Magento\Signifyd\Model\Guarantee\CancelGuaranteeAbility;

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
    private $caseEntity = false;

    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @var CreateGuaranteeAbility
     */
    private $createGuaranteeAbility;

    /**
     * @var CancelGuaranteeAbility
     */
    private $cancelGuaranteeAbility;

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
     * @param CancelGuaranteeAbility $cancelGuaranteeAbility
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CaseManagement $caseManagement,
        CreateGuaranteeAbility $createGuaranteeAbility,
        CancelGuaranteeAbility $cancelGuaranteeAbility,
        array $data = []
    ) {
        $this->config = $config;
        $this->caseManagement = $caseManagement;
        $this->createGuaranteeAbility = $createGuaranteeAbility;
        $this->cancelGuaranteeAbility = $cancelGuaranteeAbility;

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
        if ($this->caseEntity === false) {
            $this->caseEntity = $this->caseManagement->getByOrderId(
                $this->getOrderId()
            );
        }

        return $this->caseEntity;
    }

    /**
     * Default getter for case properties
     *
     * @param mixed $defaultValue
     * @param callable $callback
     * @return mixed
     */
    private function getCaseProperty($defaultValue, callable $callback)
    {
        return $this->isEmptyCase() ? $defaultValue : call_user_func($callback);
    }

    /**
     * Checks if case is exists for order
     *
     * @return bool
     */
    public function isEmptyCase()
    {
        return $this->getCaseEntity() === null;
    }

    /**
     * Gets case status
     *
     * @return string
     */
    public function getCaseStatus()
    {
        return $this->getCaseProperty('', function () {
            return $this->getCaseEntity()->getStatus();
        });
    }

    /**
     * Gets case score value
     *
     * @return int
     */
    public function getCaseScore()
    {
        return $this->getCaseProperty(0, function () {
            return $this->getCaseEntity()->getScore();
        });
    }

    /**
     * Gets state of case guarantee eligible.
     *
     * @return string|\Magento\Framework\Phrase
     */
    public function getCaseGuaranteeEligible()
    {
        return $this->getCaseProperty('', function () {
            $value = $this->getCaseEntity()->isGuaranteeEligible();

            if ($value === null) {
                return '';
            }

            return $value ? __('Yes') : __('No');
        });
    }

    /**
     * Gets case guarantee disposition status.
     *
     * @return string
     */
    public function getCaseGuaranteeDisposition()
    {
        return $this->getCaseProperty('', function () {
            return $this->getCaseEntity()->getGuaranteeDisposition();
        });
    }

    /**
     * Gets case review disposition status.
     *
     * @return string
     */
    public function getCaseReviewDisposition()
    {
        return $this->getCaseProperty('', function () {
            return $this->getCaseEntity()->getReviewDisposition();
        });
    }

    /**
     * Gets case create date.
     *
     * @return string
     */
    public function getCaseCreatedAt()
    {
        return $this->getCaseProperty('asd', function () {
            return $this->getCaseEntity()->getCreatedAt();
        });
    }

    /**
     * Gets case update date.
     *
     * @return string
     */
    public function getCaseUpdatedAt()
    {
        return $this->getCaseProperty('', function () {
            return $this->getCaseEntity()->getUpdatedAt();
        });
    }

    /**
     * Gets case associated team name.
     *
     * @return string
     */
    public function getCaseAssociatedTeam()
    {
        return $this->getCaseProperty('', function () {
            $teamName = 'unknown';
            $team = $this->getCaseEntity()->getAssociatedTeam();
            if (isset($team['teamName'])) {
                $teamName = $team['teamName'];
            }

            return $teamName;
        });
    }

    /**
     * Returns cell class name according to case score value.
     * It could be used by merchant to customize order view template.
     *
     * @return string
     */
    public function getScoreClass()
    {
        return $this->getCaseProperty('', function () {
            $score = $this->getCaseEntity()->getScore();

            if (self::$scoreAccept <= $score) {
                $result = 'green';
            } elseif ($score <= self::$scoreDecline) {
                $result = 'red';
            } else {
                $result = 'yellow';
            }

            return $result;
        });
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

        if ($this->cancelGuaranteeAbility->isAvailable($this->getOrderId())) {
            $buttons[] = $this->getCancelButton();
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
            'title' => __('Submit Guarantee Request'),
            'url' => $this->getUrl('signifyd/guarantee/create'),
            'componentName' => 'submit_guarantee_request',
            'orderId' => $this->getOrderId()
        ];
    }

    /**
     * Returns configuration for cancel Guarantee request button.
     *
     * @return array
     */
    private function getCancelButton()
    {
        return [
            'title' => __('Cancel Guarantee Request'),
            'url' => $this->getUrl('signifyd/guarantee/cancel'),
            'componentName' => 'cancel_guarantee_request',
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
