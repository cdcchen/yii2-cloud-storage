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

    /**
     * @param $filename
     * @return string
     */
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
     * @param string $body
     * @param string $filename
     * @param string $prefix
     * @param string $suffix
     * @return array
     * @throws \ErrorException
     */
    protected function writeFile($body, $filename = null, $prefix = null, $suffix = null)
    {
        if (empty($filename) && !$this->autoGenerateFilename) {
            throw new \InvalidArgumentException('$filename is required when autoGenerateFilename is false.');
        }

        if (empty($filename)) {
            $builder = new PathBuilder();
            $builder->buildPathName($this->pathFormat, $prefix, $suffix)->buildFileName($this->filenameFormat);

            $filename = $builder->getFilePath();
            $fileUrl = $builder->getFileUrl($this->domain);
        } else {
            $fileUrl = $this->getFileUrl($filename);
        }

        if (empty($filename) || empty($filename)) {
            throw new \InvalidArgumentException('filePath and fileName is required.');
        }

        if (is_file($body) && !is_readable($body)) {
            throw new \ErrorException('filename is unreadable.');
        }

        $result = $this->_handle->writeFile($filename, $body, $this->_options, $this->autoMkDir);

        $fileInfo = [
            'url' => $fileUrl,
            'path' => dirname($filename),
            'name' => basename($filename),
        ];

        return is_bool($result) ? $fileInfo : array_merge($fileInfo, $result);
    }

    /**
     * @param string $fileUrl
     * @return bool
     */
    protected function deleteFile($fileUrl)
    {
        $file_path = parse_url($fileUrl, PHP_URL_PATH);
        return $file_path ? $this->_handle->deleteFile($file_path) : false;
    }

    /**
     * @param string $file
     */
    protected function exifFile($file)
    {

    }
}