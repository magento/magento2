<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
