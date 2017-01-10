<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
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
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        Config $config,
        CaseManagement $caseManagement,
        array $data = []
    ) {
        $this->config = $config;
        $this->caseManagement = $caseManagement;

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
     * Checks if module is enabled.
     *
     * @return boolean
     */
    public function isModuleActive()
    {
        return $this->config->isActive();
    }

    /**
     * Gets Case entity associated with order id.
     *
     * @return CaseInterface|null
     */
    public function getCaseEntity()
    {
        return $this->caseManagement->getByOrderId(
            $this->getOrder()->getEntityId()
        );
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
     * Gets state of case guarantee eligible.
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
     * It could be used by merchant to customize order view skin.
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
}
