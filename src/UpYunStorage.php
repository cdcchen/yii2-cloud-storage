<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 14/12/20
 * Time: 下午11:53
 */

namespace cdcchen\cloudstorage;

use cdcchen\filesystem\PathBuilder;
use cdcchen\upyun\UpYunClient;
use yii\base\InvalidConfigException;

/**
 * Class UpYunStorage
 * @package cdcchen\cloudstorage
 */
class UpYunStorage extends Storage
{
    /**
     * @var string
     */
    public $bucket;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var
     */
    public $domain;

    /**
     * @var bool
     */
    public $autoGenerateFilename = false;

    /**
     * @var string
     */
    public $pathFormat;

    /**
     * @var string
     */
    public $filenameFormat;

    /**
     * @var bool
     */
    public $autoMkDir = true;

    /**
     * @var null|string
     */
    public $endpoint = null;

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

        if (empty($this->bucket) || empty($this->username) || empty($this->domain)) {
            throw new InvalidConfigException('bucket|username|domain is required');
        }

        if ($this->autoGenerateFilename && (empty($this->filenameFormat))) {
            throw new InvalidConfigException('filenameFormat is required when autoGenerateFilename is true');
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

    public function getFileUrl($filename)
    {
        return $this->domain . '/' . ltrim($filename, '/');
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
     * @param string $filename
     * @param string $file_path
     * @return array
     * @throws \ErrorException
     */
    protected function writeFile($filename, $file_path = null)
    {
        if (empty($file_path) && !$this->autoGenerateFilename) {
            throw new \InvalidArgumentException('$file_path is required when autoGenerateFilename is false.');
        }

        if (empty($file_path)) {
            $builder = new PathBuilder();
            $builder->buildPathName($this->pathFormat);
            $builder->buildFileName($this->filenameFormat);
            $file_path = $builder->getFilePath();
            $file_url = $builder->getFileUrl($this->domain);
        } else {
            $file_url = $this->getFileUrl($file_path);
        }

        if (empty($file_path) || empty($filename)) {
            throw new \InvalidArgumentException('filePath and fileName is required.');
        }

        if (is_file($filename) && !is_readable($filename)) {
            throw new \ErrorException('filename is unreadable.');
        }

        $result = $this->_handle->writeFile($file_path, $filename, $this->_options, $this->autoMkDir);
        if ($result) {
            $result['file_url'] = $file_url;
            $result['file_path'] = $file_url;
            $result['file_name'] = dirname($file_path);
        }

        return $result;
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