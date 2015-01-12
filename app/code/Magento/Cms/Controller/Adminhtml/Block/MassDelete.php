<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Block;

use Magento\Cms\Controller\Adminhtml\AbstractMassDelete;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractMassDelete
{
    /**
     * Field id
     */
    const ID_FIELD = 'block_id';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Magento\Cms\Model\Resource\Block\Collection';

    /**
     * Block model
     *
     * @var string
     */
    protected $model = 'Magento\Cms\Model\Block';
}
