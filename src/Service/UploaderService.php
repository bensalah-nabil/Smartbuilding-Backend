<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;



class UploaderService

{
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getFileName(UploadedFile $uploadedFile)
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $randomBytes = random_bytes(16);
        $randomHex = bin2hex($randomBytes);
        $extension = $uploadedFile->guessExtension();
        $fileName = Urlizer::urlize($originalFilename) . '-' . $randomHex . '.' . $extension;
        return $fileName;
    }

    public function getPath(String $fileName,String $directoryPath,UploadedFile $uploadedFile)
    {
        if (!is_dir($directoryPath)) {
            if (!mkdir($directoryPath, 0777, true)) {
                throw new \Exception(sprintf('Failed to create directory "%s".', $directoryPath));
            }
        }
        $newPath = $directoryPath . '/' . $fileName;
        if (!move_uploaded_file($uploadedFile->getPathname(), $newPath)) {
            throw new \Exception(sprintf('An error occurred while uploading the file "%s".', $uploadedFile->getClientOriginalName()));
        }
        $rootDir = $this->params->get('kernel.project_dir');
        $relativePath = str_replace($rootDir . '/public', '', $newPath);
        return $relativePath;
    }
}