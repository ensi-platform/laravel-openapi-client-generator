<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Generators;

use FilesystemIterator;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

class PhpProviderGenerator
{
   /**
    * @var string
    */
   private $packageDir;

   /**
    * @var string
    */
   private $namespace;

   /**
    * @var string
    */
   private $packageName;

   /**
    * @var string
    */
   private $apiPackage;


   public function __construct(string $packageDir, string $namespace, string $packageName, string $apiPackage)
   {
      $this->packageDir = $packageDir;
      $this->namespace = $namespace;
      $this->packageName = $packageName;
      $this->apiPackage = $apiPackage;
   }

   public function generate(): void
   {
      $apis = $this->getApis();

      $file = $this->getProviderFile($apis);

      $this->saveProviderFile($file);
   }

   private function getApis()
   {
      $apis = new FilesystemIterator(
         $this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $this->apiPackage,
         FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
      );

      return collect($apis)->map(function ($service) {
         return $service->getBasename('.php');
      });
   }

   private function getProviderFile($apiClassNames)
   {
      $file = new PhpFile();
      $namespace = $file->addNamespace($this->namespace);
      $this->addUse($namespace, $apiClassNames);

      $class = $namespace->addClass($this->getProviderName());

      //   $this->addRegisterMethod($class, $apis);

      $apiClassStrings = array_map(function ($className) {
         return "$className::class";
      }, $apiClassNames);

      $class->addProperty('apis', $apiClassStrings)
         ->setPublic()
         ->setStatic()
         ->addComment('@var string[]');

      // $class->addProperty('configuration', $apiClassStrings)
      //    ->setPublic()
      //    ->setStatic()
      //    ->addComment('@var string[]');

      return $file;
   }

   private function saveProviderFile($file)
   {
      $printer = new PsrPrinter();
      $serviceProviderName = $this->getProviderName();
      dd($this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $serviceProviderName . ".php");
      file_put_contents($this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $serviceProviderName . ".php", $printer->printFile($file));
   }

   private function addUse($namespace, $apis): void
   {
      foreach ($apis as $api) {
         $namespace->addUse("$this->namespace\\$this->apiPackage\\$api");
      }
   }

   private function getProviderName(): string
   {
      return "{$this->packageName}Provider";
   }
}
