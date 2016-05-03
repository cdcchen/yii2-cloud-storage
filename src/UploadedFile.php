<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/2/26
 * Time: 16:51
 */

namespace cdcchen\yii\cloudstorage;


use yii\base\ErrorException;
use yii\web\UploadedFile as YiiUploadFile;

/**
 * Class UploadedFile
 * @package cdcchen\yii\cloudstorage
 */
class UploadedFile extends YiiUploadFile
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @param string $filename
     * @param string $prefix
     * @param string $suffix
     * @param bool $deleteTempFile
     * @return array|bool Array key is width, height, type, frames, url, file, path, name
     * @throws ErrorException
     */
    public function upload($filename = null, $prefix = null, $suffix = null, $deleteTempFile = true)
    {
        if ($this->error == UPLOAD_ERR_OK && is_uploaded_file($this->tempName) && $this->beforeUpload()) {
            $result = $this->uploadFile($filename, $prefix, $suffix);

            if ($deleteTempFile && is_writable($this->tempName)) {
                unlink($this->tempName);
            }

            return $result;
        }

        return false;
    }

    /**
     * @param Storage $storage
     * @return $this
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @param string $file
     * @param string $prefix
     * @param string $suffix
     * @return bool|array Array key is width, height, type, frames, url, file, path, name
     */
    protected function uploadFile($file, $prefix, $suffix)
    {
        return $this->storage->write($this->tempName, $file, $this->getExtension(), $prefix, $suffix);
    }

    /**
     * @return bool
     * @throws ErrorException
     */
    protected function beforeUpload()
    {
        if ($this->storage instanceof Storage)
            return true;

        throw new ErrorException('Please call setStorage method to set cloud storage instance.');
    }
}