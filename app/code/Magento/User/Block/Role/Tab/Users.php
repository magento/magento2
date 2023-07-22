<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\Role\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\User\Block\Role\Grid\User;
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class Users extends Tabs
{
    /**
     * User model factory
     *
     * @var CollectionFactory
     */
    protected $_userCollectionFactory;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param CollectionFactory $userCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        CollectionFactory $userCollectionFactory,
        array $data = []
    ) {
        // _userCollectionFactory is used in parent::__construct
        $this->_userCollectionFactory = $userCollectionFactory;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $roleId = $this->getRequest()->getParam('rid', false);
        /** @var UserCollection $users */
        $users = $this->_userCollectionFactory->create()->load();
        $this->setTemplate('Magento_User::role/users.phtml')
             ->assign('users', $users->getItems())
             ->assign('roleId', $roleId);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'userGrid',
            $this->getLayout()->createBlock(User::class, 'roleUsersGrid')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('userGrid');
    }
}
