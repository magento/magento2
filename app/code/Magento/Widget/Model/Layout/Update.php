<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Layout;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Widget\Model\ResourceModel\Layout\Update as ResourceUpdate;

/**
 * Layout Update model class
 *
 * @method int getIsTemporary() getIsTemporary()
 * @method int getLayoutLinkId() getLayoutLinkId()
 * @method string getUpdatedAt() getUpdatedAt()
 * @method string getXml() getXml()
 * @method Update setIsTemporary() setIsTemporary(int $isTemporary)
 * @method Update setHandle() setHandle(string $handle)
 * @method Update setXml() setXml(string $xml)
 * @method Update setStoreId() setStoreId(int $storeId)
 * @method Update setThemeId() setThemeId(int $themeId)
 * @method Update setUpdatedAt() setUpdatedAt(string $updateDateTime)
 */
class Update extends AbstractModel
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Layout Update model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceUpdate::class);
    }
}
