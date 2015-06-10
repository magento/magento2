<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Config\Backend;

/**
 * Backend model for validating ip addresses entered in Developer Client Restrictions
 *
 * Class AllowedIps
 */
class AllowedIps extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Validate ip addresses before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $allowedIpsRaw = $this->getValue();
        $noticeMsgArray = [];
        $allowedIpsArray = [];

        if (empty($allowedIpsRaw)) {
            return parent::beforeSave();
        }

        $dataArray = explode(',', $allowedIpsRaw);
        foreach ($dataArray as $data) {
            if (filter_var(trim($data), FILTER_VALIDATE_IP)) {
                $allowedIpsArray[] = $data;
            } else {
                $noticeMsgArray[] = $data;
            }
        }

        $noticeMsg = implode(',', $noticeMsgArray);
        if (!empty($noticeMsgArray)) {
            $this->messageManager->addNotice(
                __(
                    __('The following invalid values cannot be saved: %values', ['values' => $noticeMsg])
                )
            );
        }

        $this->setValue(implode(',', $allowedIpsArray));
        return parent::beforeSave();
    }
}
