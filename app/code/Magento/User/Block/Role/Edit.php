<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\Role;

class Edit extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
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
            $this->getLayout()->createBlock('Magento\User\Block\Role\Tab\Info')->setRole($role)->setActive(true)
        );

        if ($role->getId()) {
            $this->addTab(
                'roles',
                [
                    'label' => __('Role Users'),
                    'title' => __('Role Users'),
                    'content' => $this->getLayout()->createBlock(
                        'Magento\User\Block\Role\Tab\Users',
                        'role.users.grid'
                    )->toHtml()
                ]
            );
        }

        return parent::_prepareLayout();
    }
}
