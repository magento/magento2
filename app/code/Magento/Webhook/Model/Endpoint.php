<?php
/**
 * Represents an endpoint to which messages can be sent
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
 *
 * @method string getName()
 * @method \Magento\Webhook\Model\Endpoint setName(string $value)
 * @method \Magento\Webhook\Model\Endpoint setEndpointUrl(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Webhook\Model\Endpoint setUpdatedAt(string $value)
 * @method \Magento\Webhook\Model\Endpoint setFormat(string $value)
 * @method string getApiUserId()
 * @method \Magento\Webhook\Model\Endpoint setApiUserId(string $value)
 * @method \Magento\Webhook\Model\Endpoint setAuthenticationType(string $value)
 * @method \Magento\Webhook\Model\Endpoint setTimeoutInSecs(string $value)
 */
namespace Magento\Webhook\Model;

class Endpoint extends \Magento\Core\Model\AbstractModel implements \Magento\Outbound\EndpointInterface
{
    /**
     * Used to create a User abstraction from a given webapi user associated with this subscription.
     * @var \Magento\Webhook\Model\User\Factory
     */
    private $_userFactory;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Webhook\Model\User\Factory $userFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Webhook\Model\User\Factory $userFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_userFactory = $userFactory;
    }

    /**
     * Initialize model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Webhook\Model\Resource\Endpoint');
    }

    /**
     * Return subscription endpoint url for compatibility with interface
     *
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->getData('endpoint_url');
    }

    /**
     * Return subscription timeout in secs for compatibility with interface
     *
     * @return string
     */
    public function getTimeoutInSecs()
    {
        return $this->getData('timeout_in_secs');
    }

    /**
     * Prepare data to be saved to database
     *
     * @return \Magento\Core\Model\AbstractModel
     * @throws \Magento\Webhook\Exception
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->hasAuthenticationType()) {
            $this->setAuthenticationType(\Magento\Outbound\EndpointInterface::AUTH_TYPE_NONE);
        }

        if ($this->hasDataChanges()) {
            $this->setUpdatedAt($this->_getResource()->formatDate(time()));
        }

        return $this;
    }

    /**
     * Returns the format this message should be sent in (JSON, XML, etc.)
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->getData('format');
    }

    /**
     * Returns the user abstraction associated with this subscription or null if no user has been associated yet.
     *
     * @return \Magento\Outbound\UserInterface|null
     */
    public function getUser()
    {
        if ($this->getApiUserId() === null) {
            return null;
        }
        return $this->_userFactory->create($this->getApiUserId());
    }

    /**
     * Returns the type of authentication to use when attaching authentication to a message
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return $this->getData('authentication_type');
    }
}
