<?php
/**
 * Provides the access to collection of job items from Magento database under \Magento\PubSub\Job\QueueReaderInterface
 *
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Job;

class QueueReader implements \Magento\PubSub\Job\QueueReaderInterface
{
    /** @var \Magento\Webhook\Model\Resource\Job\Collection */
    protected $_collection;

    /** @var \ArrayIterator */
    protected $_iterator;

    /**
     * Initialize model
     *
     * @param \Magento\Webhook\Model\Resource\Job\Collection $collection
     */
    public function __construct(\Magento\Webhook\Model\Resource\Job\Collection $collection)
    {
        $this->_collection = $collection;
        $this->_iterator = $this->_collection->getIterator();
    }

    /**
     * Return the top job from the queue.
     *
     * @return \Magento\PubSub\JobInterface|null
     */
    public function poll()
    {
        if ($this->_iterator->valid()) {
            /** @var $job \Magento\PubSub\JobInterface */
            $job = $this->_iterator->current();
            $this->_iterator->next();
            return $job;
        }
        return null;
    }
}
