<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Helper\Admin;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\Guarantee\SubmitEligible;

/**
 * Get Signifyd Case Info
 */
class CaseInfo extends AbstractOrder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @var SubmitEligible
     */
    private $submitEligible;

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
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param Config $config
     * @param CaseManagement $caseManagement
     * @param SubmitEligible $submitEligible
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        Config $config,
        CaseManagement $caseManagement,
        SubmitEligible $submitEligible,
        array $data = []
    ) {
        $this->config = $config;
        $this->caseManagement = $caseManagement;
        $this->submitEligible = $submitEligible;

        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Retrieve required options from parent
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the parent block for this block.')
            );
        }
        $this->setOrder($this->getParentBlock()->getOrder());

        foreach ($this->getParentBlock()->getOrderInfoData() as $key => $value) {
            $this->setDataUsingMethod($key, $value);
        }

        parent::_beforeToHtml();
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
    public function getCaseEntity()
    {
        return $this->caseManagement->getByOrderId($this->getOrderId());
    }

    /**
     * Gets state of case guarantee eligible.
     *
     * @param CaseInterface $caseEntity
     * @return \Magento\Framework\Phrase
     */
    public function getGuaranteeEligible(CaseInterface $caseEntity)
    {
        return $caseEntity->isGuaranteeEligible() ? __('Yes') : __('No');
    }

    /**
     * Gets associated team name.
     *
     * @param CaseInterface $caseEntity
     * @return string
     */
    public function getAssociatedTeam(CaseInterface $caseEntity)
    {
        $result = 'unknown';
        $team = $caseEntity->getAssociatedTeam();
        if (isset($team['teamName'])) {
            $result = $team['teamName'];
        }

        return $result;
    }

    /**
     * Returns cell class name according to case score value.
     * It could be used by merchant to customize order view template.
     *
     * @param CaseInterface $caseEntity
     * @return string
     */
    public function getScoreClass(CaseInterface $caseEntity)
    {
        $score = $caseEntity->getScore();

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

        if ($this->submitEligible->check($this->getOrderId())) {
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
        return $this->getOrder()->getEntityId();
    }
}
