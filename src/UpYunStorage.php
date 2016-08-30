<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 14/12/20
 * Time: 下午11:53
 */

namespace cdcchen\yii\cloudstorage;

use cdcchen\filesystem\PathBuilder;
use cdcchen\upyun\av\FetchFileTask;
use cdcchen\upyun\AVClient;
use cdcchen\upyun\UpYunClient;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * Class UpYunStorage
 * @package cdcchen\cloudstorage
 */
class UpYunStorage extends BaseStorage
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
     * @var bool
     */
    public $enableSSL = false;

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

        if (stripos($this->domain, 'http:') !== 0 && stripos($this->domain, 'https:') !== 0) {
            $this->domain = ($this->enableSSL ? 'https' : 'http') . '://' . ltrim($this->domain, '/');
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
     * @param null|string $prefix
     * @param null|string $suffix
     * @param string $extensionName
     * @param null|string $filename
     * @return PathBuilder
     */
    public function getAutoPathBuilder($prefix = null, $suffix = null, $extensionName = '', $filename = null)
    {
        if (empty($extensionName) && $filename) {
            $extensionName = $this->getExtensionName($filename);
        }
        $builder = new PathBuilder();
        $builder->buildPathName($this->pathFormat, $prefix, $suffix)
                ->buildFileName($this->filenameFormat, $extensionName, false);

        return $builder;
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
     * @param string $body filename or file content
     * @param string $filename
     * @param string $extensionName
     * @param string $prefix
     * @param string $suffix
     * @return array
     * @throws \ErrorException
     */
    protected function writeFile($body, $filename = null, $extensionName = '', $prefix = null, $suffix = null)
    {
        if (empty($filename) && !$this->autoGenerateFilename) {
            throw new \InvalidArgumentException('$filename is required when autoGenerateFilename is false.');
        }

        if (@is_file($body) && !is_readable($body)) {
            throw new \ErrorException('filename is unreadable.');
        }

        if (empty($filename)) {
            $builder = $this->getAutoPathBuilder($prefix, $suffix, $extensionName, $body);

            $filename = $builder->getFilePath('/');
            $fileUrl = $builder->getFileUrl($this->domain);
        } else {
            $fileUrl = $this->getFileUrl($filename);
        }

        if (empty($filename) || empty($filename)) {
            throw new \InvalidArgumentException('filePath and fileName is required.');
        }

        $result = $this->_handle->writeFile($filename, $body, $this->_options, $this->autoMkDir);

        $fileInfo = [
            'url' => $fileUrl,
            'file' => $filename,
            'path' => dirname($filename),
            'name' => basename($filename),
        ];

        return is_bool($result) ? $fileInfo : array_merge($fileInfo, $result);
    }

    /**
     * @param $body
     * @return array|null
     * @throws InvalidConfigException
     */
    protected static function getExtensionName($body)
    {
        if (@is_file($body)) {
            $info = getimagesize($body);
            if (empty($info)) {
                $mimeType = FileHelper::getMimeType($body);
                if ($mimeType && $extensions = FileHelper::getExtensionsByMimeType($mimeType)) {
                    return $extensions ? current($extensions) : null;
                }
            } else {
                $mimeType = $info[2];
                return image_type_to_extension($mimeType, false);
            }
        } elseif ($info = getimagesizefromstring($body)) {
            $mimeType = $info[2];
            return image_type_to_extension($mimeType, false);
        }

        return null;
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
     * @return array
     */
    protected function exifFile($file)
    {
        return [];
    }

    /**
     * @param string $notifyUrl
     * @param FetchFileTask[] $tasks
     * @return array
     */
    public function AsyncFetchFiles($notifyUrl, array $tasks)
    {
        $client = new AVClient($this->username, $this->password);
        return $client->fetchFiles($this->bucket, $tasks, $notifyUrl);
    }

    /**
     * @param string $notifyUrl
     * @param string $url
     * @param string $saveAs
     * @param bool $random
     * @param bool $overwrite
     * @return array
     */
    public function AsyncFetchFile($notifyUrl, $url, $saveAs, $random = false, $overwrite = true)
    {
        $client = new AVClient($this->username, $this->password);
        return $client->fetchFile($this->bucket, $notifyUrl, $url, $saveAs, $random, $overwrite);
    }
}