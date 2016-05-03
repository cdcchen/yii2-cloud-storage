<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 15/5/22
 * Time: 下午3:27
 */

namespace cdcchen\yii\cloudstorage;


use yii\base\Component;

/**
 * Class Storage
 * @package cdcchen\yii\cloudstorage
 */
abstract class Storage extends Component
{
    /**
     * @param $filename
     * @return mixed
     */
    final public function read($filename)
    {
        return $this->readFile($filename);
    }

    /**
     * @param string $body
     * @param null|string $filename
     * @param null|string $extensionName
     * @param null|string $prefix
     * @param null|string $suffix
     * @return array|bool
     */
    final public function write($body, $filename = null, $extensionName = null, $prefix = null, $suffix = null)
    {
        return $this->writeFile($body, $filename, $extensionName, $prefix, $suffix);
    }

    /**
     * @param string $filename
     * @return bool
     */
    final public function delete($filename)
    {
        return $this->deleteFile($filename);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    final public function exif($filename)
    {
        return $this->exifFile($filename);
    }

    /**
     * @param string $filename
     * @return string
     */
    abstract protected function readFile($filename);

    /**
     * @param string $body
     * @param null|string $filename
     * @param null|string $extensionName
     * @param null|string $prefix
     * @param null|string $suffix
     * @return array|bool
     */
    abstract protected function writeFile($body, $filename = null, $extensionName = null, $prefix = null, $suffix = null);

    /**
     * @param string $filename
     * @return bool
     */
    abstract protected function deleteFile($filename);

    /**
     * @param string $filename
     * @return mixed
     */
    abstract protected function exifFile($filename);
}