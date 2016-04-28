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
    final public function write($filePath, $fileName)
    {
        return $this->writeFile($filePath, $fileName);
    }

    final public function delete($filePath)
    {
        return $this->deleteFile($filePath);
    }

    final public function exif($filePath)
    {
        return $this->exifFile($filePath);
    }

    abstract protected function writeFile($filePath, $fileName);
    abstract protected function deleteFile($filePath);
    abstract protected function exifFile($filePath);
}