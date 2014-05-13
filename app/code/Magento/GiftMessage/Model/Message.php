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
namespace Magento\GiftMessage\Model;

/**
 * Gift Message model
 *
 * @method \Magento\GiftMessage\Model\Resource\Message _getResource()
 * @method \Magento\GiftMessage\Model\Resource\Message getResource()
 * @method int getCustomerId()
 * @method \Magento\GiftMessage\Model\Message setCustomerId(int $value)
 * @method string getSender()
 * @method \Magento\GiftMessage\Model\Message setSender(string $value)
 * @method string getRecipient()
 * @method \Magento\GiftMessage\Model\Message setRecipient(string $value)
 * @method string getMessage()
 * @method \Magento\GiftMessage\Model\Message setMessage(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Message extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\GiftMessage\Model\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Model\Resource\Message $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param \Magento\GiftMessage\Model\TypeFactory $typeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Model\Resource\Message $resource,
        \Magento\Framework\Data\Collection\Db $resourceCollection,
        \Magento\GiftMessage\Model\TypeFactory $typeFactory,
        array $data = array()
    ) {
        $this->_typeFactory = $typeFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\GiftMessage\Model\Resource\Message');
    }

    /**
     * Return model from entity type
     *
     * @param string $type
     * @return mixed
     */
    public function getEntityModelByType($type)
    {
        return $this->_typeFactory->createType($type);
    }

    /**
     * Checks thats gift message is empty
     *
     * @return bool
     */
    public function isMessageEmpty()
    {
        return trim($this->getMessage()) == '';
    }
}
