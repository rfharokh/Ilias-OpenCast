<?php

use srag\Plugins\Opencast\UI\Input\Plupload;

/**
 * Class xoctUploadFile
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xoctUploadFile {

	/**
	 * @param $name
	 *
	 * @return xoctUploadFile
	 */
	public static function getInstanceFromFileArray($name) {
		$file = $_POST[$name];

		$inst = new self();
		$inst->setTitle($file['name']);
		$inst->setTmpName($file['tmp_name']);
		$inst->setFileSize($file['size']);
		$inst->setPostVar($name);

		return $inst;
	}


	/**
	 * @return CURLFile
	 */
	public function getCURLFile() {
		$plupload = new Plupload();
		$CURLFile = new CURLFile($plupload->getTargetDir() . '/' . $this->getTmpName());

		return $CURLFile;
	}


	/**
	 * @var string
	 */
	protected $tmp_name = '';
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var int
	 */
	protected $file_size = 0;
	/**
	 * @var string
	 */
	protected $post_var = '';
	/**
	 * @var string
	 */
	protected $mime_type = '';


	/**
	 * @return string
	 */
	public function getTmpName() {
		return $this->tmp_name;
	}


	/**
	 * @param string $tmp_name
	 */
	public function setTmpName($tmp_name) {
		$this->tmp_name = $tmp_name;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return int
	 */
	public function getFileSize() {
		return $this->file_size;
	}


	/**
	 * @param int $file_size
	 */
	public function setFileSize($file_size) {
		$this->file_size = $file_size;
	}


	/**
	 * @return string
	 */
	public function getPostVar() {
		return $this->post_var;
	}


	/**
	 * @param string $post_var
	 */
	public function setPostVar($post_var) {
		$this->post_var = $post_var;
	}


	/**
	 * @return string
	 */
	public function getMimeType() {
		return $this->mime_type;
	}


	/**
	 * @param string $mime_type
	 */
	public function setMimeType($mime_type) {
		$this->mime_type = $mime_type;
	}
}

?>
