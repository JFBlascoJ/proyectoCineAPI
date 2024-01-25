<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit14bc52e06e68843617e380ee79280efe
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Vespertino\\Api\\' => 15,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Vespertino\\Api\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit14bc52e06e68843617e380ee79280efe::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit14bc52e06e68843617e380ee79280efe::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit14bc52e06e68843617e380ee79280efe::$classMap;

        }, null, ClassLoader::class);
    }
}
