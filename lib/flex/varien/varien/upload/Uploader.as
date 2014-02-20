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
	import flash.errors.IOError;
	import flash.errors.IllegalOperationError;
	import flash.events.DataEvent;
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.events.ProgressEvent;
	import flash.events.SecurityErrorEvent;
	import flash.net.FileFilter;
	import flash.net.FileReference;
	import flash.net.FileReferenceList;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;

	import mx.core.IMXMLObject;
	import mx.utils.ObjectUtil;


	/**
	 *  @eventType varien.upload.UploaderEvent.OPEN
	 */
	[Event(name='open', type='varien.upload.UploaderEvent')]

	/**
	 *  @eventType varien.upload.UploaderEvent.RESET
	 */
	[Event(name='reset', type='varien.upload.UploaderEvent')]

	/**
	 *  @eventType varien.upload.UploaderEvent.REMOVE
	 */
	[Event(name='remove', type='varien.upload.UploaderEvent')]

	/**
	 *  @eventType varien.upload.UploaderEvent.PROGRESS
	 */
	[Event(name='progress', type='varien.upload.UploaderEvent')]

	/**
	 *  @eventType varien.upload.UploaderEvent.ERROR
	 */
	[Event(name='error', type='varien.upload.UploaderEvent')]

	/**
	 *  @eventType varien.upload.UploaderEvent.SELECT
	 */
	[Event(name='select', type='varien.upload.UploaderEvent')]

	/**
	 *  @eventType varien.upload.UploaderEvent.COMPLETE
	 */
	[Event(name='complete', type='varien.upload.UploaderEvent')]

	/**
	 *  @eventType varien.upload.UploaderEvent.CANCEL
	 */
	[Event(name='cancel', type='varien.upload.UploaderEvent')]

	public class Uploader extends EventDispatcher implements IMXMLObject
	{
		public var document:Object;

		public var id:String;

		public var useTypeFilter:Boolean;

		protected var _fileList:FileReferenceList;
		protected var _typeFilters:Object;
		protected var _config:Object;
		protected var _files:Object;
		protected var _counter:uint;
		protected var _laterUploadStack:Array;

		public static const FILE_NEW:String 	 	  = 'new';
		public static const FILE_PROGRESS:String      = 'progress';
		public static const FILE_COMPLETE:String      = 'complete';
		public static const FILE_FULL_COMPLETE:String = 'full_complete';
		public static const FILE_ERROR:String    	  = 'error';

		public static const ERROR_ZERO_SIZE:Number	  = 1;
		public static const ERROR_HTTP:Number	  	  = 2;
		public static const ERROR_IO:Number	  	      = 3;
		public static const ERROR_SECURITY:Number	  = 4;
		public static const ERROR_SSL:Number	  	  = 5;

		/**
		 * Constructor
		 *
		 * @param config configuration of uploader
		 */
		public function Uploader(config:Object=null)
		{
			super();
			_fileList 	   = new FileReferenceList();
			_files 		   = {};
			_typeFilters   = {};
			_config		   = {};
			useTypeFilter  = false;
			_laterUploadStack = [];
			_fileList.addEventListener(Event.SELECT, _handleSelect);
		}

		/**
		 * Implementing of IMXMLObject
		 *
		 * @see IMXMLObject
		 */
		public function initialized(document:Object, id:String):void
		{
			this.document = document;
			this.id = id;
		}

		/**
		 * Browse files for upload
		 */
		public function browse():void
		{
			_fileList.browse(useTypeFilter ? getTypeFiltersArray() : null);
		}

		/**
		 * Add file filter for uploader
		 *
		 * @param filterId filter unique id
		 * @param filterLabel label that will be showed in dialog box
		 * @param fileTypes array of file filter masks
		 */
		public function addTypeFilter(filterId:String, filterLabel:String, fileTypes:Array):void
		{
			_typeFilters[filterId] = new FileFilter(filterLabel, fileTypes.join(';'));
		}

		/**
		 * Retrieve file filters as array
		 */
		public function getTypeFiltersArray():Array
		{
			var filters:Array = new Array();
			for each (var typeFilter:FileFilter in _typeFilters) {
				filters.push(typeFilter);
			}
			return filters;
		}

		/**
		 * Retrieve file filters as object
		 */
		public function getTypeFilters():Object
		{
			return _typeFilters;
		}

		/**
		 * Retrieve file filter with specified id
		 *
		 * @param filterId filter unique id
		 */
		public function getTypeFilter(filterId:String):FileFilter
		{
			return hasTypeFilter(filterId) ? _typeFilters[filterId] : null;
		}

		/**
		 * Check if filter with specified id exists
		 *
		 * @param filterId filter unique id
		 */
		public function hasTypeFilter(filterId:String):Boolean
		{
			return (_typeFilters[filterId] is FileFilter);
		}

		/**
		 * Remove type filter with specified id
		 *
		 * @param filterId filter unique id
		 */
		public function removeTypeFilter(filterId:String):void
		{
			if (hasTypeFilter(filterId)) {
				delete _typeFilters[filterId];
			}
		}

		/**
		 * Upload configuration property
		 *
		 * For example:
		 * uploadObject.config = {
		 * 		url:		'http://myhost.com/fileUpload.php',
		 * 		params: 	{test:1},
		 * 		file_field:	'file'
		 * };
		 */

		public function set config(value:Object):void
		{
			for (var property:String in value) {
				_config[property] = value[property];
			}
		}

		public function get config():Object
		{
			return _config;
		}

		/**
		 * Retrieve file info with specified id
		 *
		 * @param id file id
		 */
		public function getFileInfo(id:String):Object
		{
			var file:FileReference = _getFileById(id);
			if (file) {
				return _collectFileInfo(id);
			}
			return null;
		}

		/**
		 * Retrieve file info for all files as array
		 */
		public function get filesInfo():Array
		{
			var result:Array = [];
			for (var id:String in _files) {
				result.push(_collectFileInfo(id));
			}
			return result;
		}

		/**
		 * Removes file info for file with specified id
		 */
		public function removeFiles():void
		{
			for (var id:String in _files) {
				delete _files[id];
			}
			_createEvent(UploaderEvent.REMOVE_ALL);
		}

		/**
		 * Removes file info for file with specified id
		 *
		 * @param id file id
		 */
		public function removeFile(id:String):void
		{
			if(_files[id]) {
				delete _files[id];
			}
			_createEvent(UploaderEvent.REMOVE);
		}

		/**
		 * Start uploading of files
		 */
		public function upload():void
		{
			if (config) {
				for(var id:String in _files) {
					if(_files[id].status == Uploader.FILE_NEW
					    ||
					    (_files[id].status == Uploader.FILE_ERROR
					     && _files[id].errorCode!=Uploader.ERROR_ZERO_SIZE)) {
							_uploadOneFile(id);
					}
				}
			}
		}

		protected function _uploadOneFile(id:String):void
		{
			var request:URLRequest = new URLRequest(config.url);
			request.method = URLRequestMethod.POST;
			request.data = new URLVariables();
			if (config.params) {
				for (var property:String in config.params) {
					request.data[property] = config.params[property];
				}
			}

			var file:FileReference = _files[id].file as FileReference;

			file.addEventListener(
				ProgressEvent.PROGRESS,
				_handleProgress
			);

			file.addEventListener(
				DataEvent.UPLOAD_COMPLETE_DATA,
				_handleComplete
			);

			file.addEventListener(
				Event.COMPLETE,
				_handlePartialComplete
			);

			file.addEventListener(
				IOErrorEvent.IO_ERROR,
				_handleIOError
			);

			file.addEventListener(
				SecurityErrorEvent.SECURITY_ERROR,
				_handleSecurityError
			);

			file.addEventListener(
				HTTPStatusEvent.HTTP_STATUS,
				_handleHttpStatus
			);
			_files[id].status = Uploader.FILE_PROGRESS;
			_files[id].uploadTry ++;
			file.upload(request, config.file_field);
		}

		/**
		 * Collects file info from FileReference object with specified id
		 *
		 * @param id file id
		 */
		protected function _collectFileInfo(id:String):Object
		{
			var info:Object = {};
			info.id      = id;
			info.name 	 = _files[id].file.name;
			try {
				info.size = _files[id].file.size;
			} catch (exception:IOError) {
				info.size = 0;
			}
			info.creator  	= _files[id].file.creator;
			info.status	  	= _files[id].status;
			info.error	  	= _files[id].error;
			info.errorCode	= _files[id].errorCode;
			info.progress 	= _files[id].progress;
			info.http	  	= _files[id].http;
			info.response 	= _files[id].response;
			return info;
		}

		/**
		 * Generates unique file id
		 *
		 * @param file FileReference
		 */
		protected function _uniqueFileId(file:FileReference):String
		{
			return 'file_' + uint(_counter++).toString();
		}

		/**
		 * Retrieve file reference by id
		 *
		 * @param id file id
		 */
		protected function _getFileById(id:String):FileReference
		{
			if(_files[id]) {
				return _files[id].file;
			}
			return null;
		}

		/**
		 * Retrieve file id by reference
		 *
		 * @param file FileReference
		 */
		protected function _getIdByFile(file:FileReference):String
		{
			for (var id:String in _files) {
				if(_files[id].file===file) {
					return id;
				}
			}
			return null;
		}

		/**
		 * Create and dispatch UploadEvent with specified type
		 *
		 * @param eventType type of UploadEvent
		 */
		protected function _createEvent(eventType:String, fileId:String=null):void
		{
			var event:UploaderEvent = new UploaderEvent(eventType);
			if(fileId === null) {
				event.data = {files: filesInfo};
			} else {
				event.data = {file : getFileInfo(fileId)};
			}
			dispatchEvent(event);
		}

		/**
		 * Property indicates that all passed for upload file uploaded successfully
		 */
		public function get allComplete():Boolean
		{
			for (var id:String in _files) {
				if (_files[id].status == Uploader.FILE_PROGRESS
					|| _files[id].status == Uploader.FILE_COMPLETE) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Property indicates that all passed for upload file has IO Error
		 * This problem only on servers with self-signed sertificates.
		 */
		public function get allIOError():Boolean
		{
			for (var id:String in _files) {
				if (_files[id].status == Uploader.FILE_PROGRESS
					 && _laterUploadStack.indexOf(id)===-1) {
					return false;
				}
			}

			return true;
		}

		protected function _handleSelect(event:Event):void
		{
			for each (var file:FileReference in _fileList.fileList) {
				// Try to catch bad files
				var badFile:Boolean = false;
				var zeroSize:Boolean = false;
				try {
					file.size;
				}
				catch (exception:IllegalOperationError) {
					badFile = true;
				}
				catch (exception:IOError) { // If file size == 0
					zeroSize = true;
				}

				if(!badFile) {
					var id:String = _uniqueFileId(file);
					_files[id] = {status:Uploader.FILE_NEW, file:file, uploadTry:0};
					if(zeroSize) {
						_files[id].status 	  = Uploader.FILE_ERROR;
						_files[id].errorCode  = Uploader.ERROR_ZERO_SIZE;
						_files[id].error	  = 'File size should be more than 0 bytes';
					}
				}
			}
			_createEvent(UploaderEvent.SELECT);
		}

		protected function _handleProgress(event:ProgressEvent):void
		{
			var id:String = _getIdByFile(event.target as FileReference);
			if (_files[id]) {
				_files[id].progress = {total: event.bytesTotal, loaded: event.bytesLoaded};
			}
			_createEvent(UploaderEvent.PROGRESS, id);
		}

		protected function _handleComplete(event:DataEvent):void
		{
			var id:String = _getIdByFile(event.target as FileReference);
			if (_files[id]) {
				_files[id].status   = Uploader.FILE_FULL_COMPLETE;
				_files[id].progress = {total: _files[id].size, loaded: _files[id].size};
				_files[id].response = event.data;
				_files[id].http	    = 200;
			}

			_uploadFromLaterStack();
			_createEvent(UploaderEvent.PROGRESS, id);
			if (allComplete) {
				_createEvent(UploaderEvent.COMPLETE);
			}
		}

		protected function _handlePartialComplete(event:Event):void
		{
			var id:String = _getIdByFile(event.target as FileReference);
			if(_files[id]) {
				_files[id].progress = {total: _files[id].size, loaded: _files[id].size};
				_files[id].status   = Uploader.FILE_COMPLETE;
			}
			_createEvent(UploaderEvent.PROGRESS, id);
		}

		protected function _handleIOError(event:IOErrorEvent):void
		{
			var id:String = _getIdByFile(event.target as FileReference);
			if(_files[id]) {
				if(_files[id].status != Uploader.FILE_ERROR){
					if (_files[id].uploadTry > 1) {
						_files[id].status    = Uploader.FILE_ERROR;
						_files[id].error	 = 'I/O Error';
						_files[id].errorCode = Uploader.ERROR_IO;
					} else {
						_resetFileHandlers(id);
						_laterUploadStack.push(id);
						if (allIOError) {
							_markAsSSLError();
						}
						return;
					}
				}
			}
			_createEvent(UploaderEvent.ERROR, id);
		}

		protected function _markAsSSLError():void
		{
			for (var id:String in _files) {
				if (_files[id].status == Uploader.FILE_PROGRESS) {
					_files[id].status    = Uploader.FILE_ERROR;
					_files[id].error	 = 'SSL self-signed sertificate error';
					_files[id].errorCode = Uploader.ERROR_SSL;
					_createEvent(UploaderEvent.ERROR, id);
				}
			}
		}

		protected function _uploadFromLaterStack():void
		{
			if (_laterUploadStack.length > 0) {
				_uploadOneFile(_laterUploadStack.shift());
			}
		}

		protected function _resetFileHandlers(id:String):void
		{
			var file:FileReference = (_files[id].file as FileReference);

			file.removeEventListener(
				ProgressEvent.PROGRESS,
				_handleProgress
			);

			file.removeEventListener(
				DataEvent.UPLOAD_COMPLETE_DATA,
				_handleComplete
			);

			file.removeEventListener(
				Event.COMPLETE,
				_handlePartialComplete
			);

			file.removeEventListener(
				IOErrorEvent.IO_ERROR,
				_handleIOError
			);

			file.removeEventListener(
				SecurityErrorEvent.SECURITY_ERROR,
				_handleSecurityError
			);

			file.removeEventListener(
				HTTPStatusEvent.HTTP_STATUS,
				_handleHttpStatus
			);
		}

		protected function _handleSecurityError(event:SecurityErrorEvent):void
		{
			var id:String = _getIdByFile(event.target as FileReference);
			if (_files[id]) {
				_files[id].status    = Uploader.FILE_ERROR;
				_files[id].error	 = 'Security Error';
				_files[id].errorCode = Uploader.ERROR_SECURITY;
			}
			_createEvent(UploaderEvent.ERROR, id);
		}

		protected function _handleHttpStatus(event:HTTPStatusEvent):void
		{
			var id:String = _getIdByFile(event.target as FileReference);
			if (_files[id]) {
				if (_files[id].status != Uploader.FILE_ERROR){
					_files[id].status    = Uploader.FILE_ERROR;
					_files[id].error     = 'Http Status Error';
					_files[id].errorCode = Uploader.ERROR_HTTP;
				}
				_files[id].http	    = event.status;
			}
			_createEvent(UploaderEvent.ERROR, id);
		}

	}

}
