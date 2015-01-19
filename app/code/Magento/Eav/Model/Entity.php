<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

/**
 * EAV entity model
 *
 */
class Entity extends \Magento\Eav\Model\Entity\AbstractEntity
{
    const DEFAULT_ENTITY_MODEL = 'Magento\Eav\Model\Entity';

    const DEFAULT_ATTRIBUTE_MODEL = 'Magento\Eav\Model\Entity\Attribute';

    const DEFAULT_BACKEND_MODEL = 'Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend';

    const DEFAULT_FRONTEND_MODEL = 'Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend';

    const DEFAULT_SOURCE_MODEL = 'Magento\Eav\Model\Entity\Attribute\Source\Config';

    const DEFAULT_ENTITY_TABLE = 'eav_entity';

    const DEFAULT_ENTITY_ID_FIELD = 'entity_id';

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        $data = []
    ) {
        parent::__construct(
            $resource,
            $eavConfig,
            $attrSetEntity,
            $localeFormat,
            $resourceHelper,
            $universalFactory,
            $data
        );
        $this->setConnection($resource->getConnection('eav_read'));
    }
}
