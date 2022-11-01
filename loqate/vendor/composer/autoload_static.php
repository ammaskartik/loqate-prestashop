<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit265d4a04a9cff8aeb6f0695be2515db4
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'LoqateFindModuleFrontController' => __DIR__ . '/../..' . '/controllers/front/Find.php',
        'LoqateRetrieveModuleFrontController' => __DIR__ . '/../..' . '/controllers/front/Retrieve.php',
        'Loqate\\ApiConnector\\Client\\Capture' => __DIR__ . '/..' . '/loqate/apiconnector/src/Client/Capture.php',
        'Loqate\\ApiConnector\\Client\\Http\\HttpClient' => __DIR__ . '/..' . '/loqate/apiconnector/src/Client/Http/HttpClient.php',
        'Loqate\\ApiConnector\\Client\\Verify' => __DIR__ . '/..' . '/loqate/apiconnector/src/Client/Verify.php',
        'Loqate\\ApiConnector\\Utils\\API' => __DIR__ . '/..' . '/loqate/apiconnector/src/Utils/API.php',
        'Validator' => __DIR__ . '/../..' . '/helper/Validator.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit265d4a04a9cff8aeb6f0695be2515db4::$classMap;

        }, null, ClassLoader::class);
    }
}
