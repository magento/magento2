<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

/**
 * EAV entity model
 *
 */
class Entity extends \Magento\Eav\Model\Entity\AbstractEntity
{
    const DEFAULT_ENTITY_MODEL = \Magento\Eav\Model\Entity::class;

    const DEFAULT_ATTRIBUTE_MODEL = \Magento\Eav\Model\Entity\Attribute::class;

    const DEFAULT_BACKEND_MODEL = \Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class;

    const DEFAULT_FRONTEND_MODEL = \Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend::class;

    const DEFAULT_SOURCE_MODEL = \Magento\Eav\Model\Entity\Attribute\Source\Config::class;

    const DEFAULT_ENTITY_TABLE = 'eav_entity';

    const DEFAULT_ENTITY_ID_FIELD = 'entity_id';

    /**
     * @param Entity\Context $context
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Eav\Model\Entity\Context $context, $data = [])
    {
        parent::__construct($context, $data);
        $this->setConnection($this->_resource->getConnection('eav'));
    }
}
