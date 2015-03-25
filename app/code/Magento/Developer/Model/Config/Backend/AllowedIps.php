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
    protected $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
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
        parent::beforeSave();
        $fieldSetData = $this->getFieldsetDataValue('allow_ips');
        $noticeMsg = '';
        $allowedIps = '';

        if (empty($fieldSetData)) {
            return $this;
        }

        $dataArray = preg_split('#\s*,\s*#', $fieldSetData, null, PREG_SPLIT_NO_EMPTY);
        foreach ($dataArray as $k => $data) {
            $data = trim(preg_replace('/\s+/','', $data));
            if ( !filter_var($data, FILTER_VALIDATE_IP) === false ) {
                $allowedIps .= (empty($allowedIps)) ? $data : "," . $data;
            } else {
                $noticeMsg .= (empty($noticeMsg)) ? $data : "," . $data;
             }
        }

        if (!empty($noticeMsg))
            $this->messageManager->addNotice(
                __(
                    'Invalid values ' . $noticeMsg . ' are not saved.'
                )
            );

        $this->setValue($allowedIps);
        return $this;
    }
}
