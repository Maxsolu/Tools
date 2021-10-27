<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9714a30d4d2809213b52558aafc5e035
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Maxsolu\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Maxsolu\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/maxsolu',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9714a30d4d2809213b52558aafc5e035::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9714a30d4d2809213b52558aafc5e035::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9714a30d4d2809213b52558aafc5e035::$classMap;

        }, null, ClassLoader::class);
    }
}