<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 15/5/22
 * Time: 下午3:27
 */

namespace cdcchen\cloudstorage;


use yii\base\Component;

abstract class Storage extends Component
{
    final public function read($filename)
    {
        return $this->readFile($filename);
    }

    final public function write($body, $filename = null, $prefix = null, $suffix = null)
    {
        return $this->writeFile($body, $filename, $prefix, $suffix);
    }

    final public function delete($filename)
    {
        return $this->deleteFile($filename);
    }

    final public function exif($filename)
    {
        return $this->exifFile($filename);
    }

    abstract protected function readFile($filename);
    abstract protected function writeFile($body, $filename = null, $prefix = null, $suffix = null);
    abstract protected function deleteFile($filename);
    abstract protected function exifFile($filename);
}