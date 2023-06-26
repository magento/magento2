<?php
namespace Webiators\CustomChanges\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'post_id';
	protected $_eventPrefix = 'webiators_myfirstmodule_post_collection';
	protected $_eventObject = 'post_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Webiators\CustomChanges\Model\Post', 'Webiators\CustomChanges\Model\ResourceModel\Post');
	}

}
