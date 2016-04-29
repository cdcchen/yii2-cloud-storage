<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 14/12/20
 * Time: 下午11:53
 */

namespace cdcchen\cloudstorage;

use cdcchen\upyun\UpYunClient;
use yii\base\InvalidConfigException;

/**
 * Class UpYunStorage
 * @package cdcchen\cloudstorage
 */
class UpYunStorage extends Storage
{
    /**
     * @var null|string
     */
    public $endpoint = null;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $bucket;

    /**
     * @var bool
     */
    public $autoMkDir = true;

    /**
     * @var bool
     */
    public $isImageBucket = true;

    /**
     * @var int
     */
    public $timeout = 60;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @var UpYunClient
     */
    protected $_handle;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (empty($this->bucket) || empty($this->username)) {
            throw new InvalidConfigException('Bucket is required');
        }

        $this->_handle = new UpYunClient($this->bucket, $this->username, $this->password, $this->endpoint,
            $this->timeout);
    }

    /**
     * @param string|array $option
     * @param mixed|null $value
     * @return $this
     */
    public function setOption($option, $value = null)
    {
        if (is_array($option)) {
            $this->_options = array_merge($this->_options, $option);
        } else {
            $this->_options[$option] = $value;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetOption()
    {
        $this->_options = [];
        return $this;
    }

    /**
     * @return UpYunClient
     */
    public function getHandle()
    {
        return $this->_handle;
    }


    /**
     * @param string $filename
     * @return string
     */
    protected function readFile($filename)
    {
        return $this->_handle->readFile($filename);
    }

    /**
     * @param string $file_path
     * @param string $filename
     * @return array
     * @throws \ErrorException
     */
    protected function writeFile($file_path, $filename)
    {
        if (empty($file_path) || empty($filename)) {
            throw new \InvalidArgumentException('filePath and fileName is required.');
        }

        if (is_file($filename) && !is_readable($filename)) {
            throw new \ErrorException('fileName is unreadable.');
        }

        return $this->_handle->writeFile($file_path, $filename, $this->_options, $this->autoMkDir);
    }

    /**
     * @param string $file_url
     * @return bool
     */
    protected function deleteFile($file_url)
    {
        $file_path = parse_url($file_url, PHP_URL_PATH);
        return $file_path ? $this->_handle->deleteFile($file_path) : false;
    }

    /**
     * @param string $file
     */
    protected function exifFile($file)
    {

    }
}