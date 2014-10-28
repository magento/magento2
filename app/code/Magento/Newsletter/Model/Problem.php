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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Newsletter\Model;

/**
 * Newsletter problem model
 *
 * @method \Magento\Newsletter\Model\Resource\Problem _getResource()
 * @method \Magento\Newsletter\Model\Resource\Problem getResource()
 * @method int getSubscriberId()
 * @method \Magento\Newsletter\Model\Problem setSubscriberId(int $value)
 * @method int getQueueId()
 * @method \Magento\Newsletter\Model\Problem setQueueId(int $value)
 * @method int getProblemErrorCode()
 * @method \Magento\Newsletter\Model\Problem setProblemErrorCode(int $value)
 * @method string getProblemErrorText()
 * @method \Magento\Newsletter\Model\Problem setProblemErrorText(string $value)
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Problem extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Current Subscriber
     *
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscriber = null;

    /**
     * Subscriber factory
     *
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_subscriberFactory = $subscriberFactory;
    }

    /**
     * Initialize Newsletter Problem Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Newsletter\Model\Resource\Problem');
    }

    /**
     * Add Subscriber Data
     *
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return $this
     */
    public function addSubscriberData(\Magento\Newsletter\Model\Subscriber $subscriber)
    {
        $this->setSubscriberId($subscriber->getId());
        return $this;
    }

    /**
     * Add Queue Data
     *
     * @param \Magento\Newsletter\Model\Queue $queue
     * @return $this
     */
    public function addQueueData(\Magento\Newsletter\Model\Queue $queue)
    {
        $this->setQueueId($queue->getId());
        return $this;
    }

    /**
     * Add Error Data
     *
     * @param \Exception $e
     * @return $this
     */
    public function addErrorData(\Exception $e)
    {
        $this->setProblemErrorCode($e->getCode());
        $this->setProblemErrorText($e->getMessage());
        return $this;
    }

    /**
     * Retrieve Subscriber
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function getSubscriber()
    {
        if (!$this->getSubscriberId()) {
            return null;
        }

        if (is_null($this->_subscriber)) {
            $this->_subscriber = $this->_subscriberFactory->create()->load($this->getSubscriberId());
        }

        return $this->_subscriber;
    }

    /**
     * Unsubscribe Subscriber
     *
     * @return $this
     */
    public function unsubscribe()
    {
        if ($this->getSubscriber()) {
            $this->getSubscriber()->setSubscriberStatus(
                \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED
            )->setIsStatusChanged(
                true
            )->save();
        }
        return $this;
    }
}
