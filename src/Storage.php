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
    final public function read($fileName)
    {
        return $this->readFile($fileName);
    }

    final public function write($filePath, $fileName)
    {
        return $this->writeFile($filePath, $fileName);
    }

    final public function delete($fileName)
    {
        return $this->deleteFile($fileName);
    }

    final public function exif($fileName)
    {
        return $this->exifFile($fileName);
    }

    abstract protected function readFile($fileName);
    abstract protected function writeFile($filePath, $fileName);
    abstract protected function deleteFile($fileName);
    abstract protected function exifFile($fileName);
}