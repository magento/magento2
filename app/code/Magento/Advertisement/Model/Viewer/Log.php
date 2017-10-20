<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Advertisement\Model\Viewer;

use Magento\Framework\DataObject;

class Log extends DataObject
{
    public function getId()
    {
        return $this->getData('id');
    }

    public function getViewerId()
    {
        return $this->getData('viewer_id');
    }

    public function getLastViewVersion()
    {
        return $this->getData('last_view_version');
    }
}
