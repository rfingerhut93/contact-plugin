<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbdfe162ddbe4637921a392e42f61cd6c
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
            'Carbon_Fields_Plugin\\' => 21,
            'Carbon_Fields\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
        'Carbon_Fields_Plugin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/wp-content/plugins/carbon-fields-plugin/core',
        ),
        'Carbon_Fields\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-fields/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbdfe162ddbe4637921a392e42f61cd6c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbdfe162ddbe4637921a392e42f61cd6c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitbdfe162ddbe4637921a392e42f61cd6c::$classMap;

        }, null, ClassLoader::class);
    }
}
