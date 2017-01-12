<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate ip addresses before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $allowedIpsRaw = $this->escaper->escapeHtml($this->getValue());
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
