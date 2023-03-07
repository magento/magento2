<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\Role;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\User\Block\Role\Tab\Info;
use Magento\User\Block\Role\Tab\Users;

/**
 * @api
 * @since 100.0.2
 */
class Edit extends Tabs
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
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
        $this->setId('role_info_tabs');
        $this->setDestElementId('role-edit-form');
        $this->setTitle(__('Role Information'));
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $role = $this->_coreRegistry->registry('current_role');

        $this->addTab(
            'info',
            $this->getLayout()->createBlock(Info::class)->setRole($role)->setActive(true)
        );

        if ($role->getId()) {
            $this->addTab(
                'roles',
                [
                    'label' => __('Role Users'),
                    'title' => __('Role Users'),
                    'content' => $this->getLayout()->createBlock(
                        Users::class,
                        'role.users.grid'
                    )->toHtml()
                ]
            );
        }

        return parent::_prepareLayout();
    }
}
