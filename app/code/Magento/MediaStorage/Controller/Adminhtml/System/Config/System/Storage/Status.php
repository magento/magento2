<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Controller\Adminhtml\System\Config\System\Storage;

/**
 * Class \Magento\MediaStorage\Controller\Adminhtml\System\Config\System\Storage\Status
 *
 * @since 2.0.0
 */
class Status extends \Magento\MediaStorage\Controller\Adminhtml\System\Config\System\Storage
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Retrieve synchronize process state and it's parameters in json format
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function execute()
    {
        $result = [];
        $flag = $this->_getSyncFlag();

        if ($flag) {
            $state = $flag->getState();

            switch ($state) {
                case \Magento\MediaStorage\Model\File\Storage\Flag::STATE_INACTIVE:
                    $flagData = $flag->getFlagData();
                    if (is_array($flagData)) {
                        if (isset($flagData['destination']) && !empty($flagData['destination'])) {
                            $result['destination'] = $flagData['destination'];
                        }
                    }
                    $state = \Magento\MediaStorage\Model\File\Storage\Flag::STATE_INACTIVE;
                    break;
                case \Magento\MediaStorage\Model\File\Storage\Flag::STATE_RUNNING:
                    if (!$flag->getLastUpdate() || time() <= strtotime(
                        $flag->getLastUpdate()
                    ) + \Magento\MediaStorage\Model\File\Storage\Flag::FLAG_TTL
                    ) {
                        $flagData = $flag->getFlagData();
                        if (is_array(
                            $flagData
                        ) && isset(
                            $flagData['source']
                        ) && !empty($flagData['source']) && isset(
                            $flagData['destination']
                        ) && !empty($flagData['destination'])
                        ) {
                            $result['message'] = __(
                                'Synchronizing %1 to %2',
                                $flagData['source'],
                                $flagData['destination']
                            );
                        } else {
                            $result['message'] = __('Synchronizing...');
                        }
                        break;
                    } else {
                        $flagData = $flag->getFlagData();
                        if (is_array(
                            $flagData
                        ) && !(isset(
                            $flagData['timeout_reached']
                        ) && $flagData['timeout_reached'])
                        ) {
                            $this->_objectManager->get(
                                \Psr\Log\LoggerInterface::class
                            )->critical(
                                new \Magento\Framework\Exception\LocalizedException(
                                    __('The timeout limit for response from synchronize process was reached.')
                                )
                            );

                            $state = \Magento\MediaStorage\Model\File\Storage\Flag::STATE_FINISHED;
                            $flagData['has_errors'] = true;
                            $flagData['timeout_reached'] = true;
                            $flag->setState($state)->setFlagData($flagData)->save();
                        }
                    }
                    // fall-through intentional
                case \Magento\MediaStorage\Model\File\Storage\Flag::STATE_FINISHED:
                case \Magento\MediaStorage\Model\File\Storage\Flag::STATE_NOTIFIED:
                    $flagData = $flag->getFlagData();
                    if (!isset($flagData['has_errors'])) {
                        $flagData['has_errors'] = false;
                    }
                    $result['has_errors'] = $flagData['has_errors'];
                    break;
                default:
                    $state = \Magento\MediaStorage\Model\File\Storage\Flag::STATE_INACTIVE;
                    break;
            }
        } else {
            $state = \Magento\MediaStorage\Model\File\Storage\Flag::STATE_INACTIVE;
        }
        $result['state'] = $state;
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
