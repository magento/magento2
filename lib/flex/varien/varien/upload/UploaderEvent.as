/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
package varien.upload
{
	import flash.events.Event;

	public class UploaderEvent extends Event
	{		
		/**
		 * @eventType progress
		 */
		public static const PROGRESS:String 	= 'progress';
		
		/**
		 * @eventType error
		 */
		public static const ERROR:String 		= 'error';

		/**
		 * @eventType select
		 */
		public static const SELECT:String 		= 'select';
		
		/**
		 * @eventType complete
		 */
		public static const COMPLETE:String 	= 'complete';
		
		/**
		 * @eventType cancel
		 */
		public static const CANCEL:String 		= 'cancel';
		
		/**
		 * @eventType remove
		 */
		public static const REMOVE:String 		= 'remove';

		/**
		 * @eventType removeall
		 */
		public static const REMOVE_ALL:String 		= 'removeall';

		protected var _data:Object;
		
		public function UploaderEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);		

		}
		
		public function get data():Object
		{
			return _data;
		}
		
		public function set data(value:Object):void
		{
			_data = value;			
		}
	}
}