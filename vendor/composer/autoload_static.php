<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite69e44c33f47bbf346dab01e67ab6f02
{
    public static $files = array (
        '253c157292f75eb38082b5acb06f3f01' => __DIR__ . '/..' . '/nikic/fast-route/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'F' => 
        array (
            'FastRoute\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'FastRoute\\' => 
        array (
            0 => __DIR__ . '/..' . '/nikic/fast-route/src',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/../..' . '/src',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite69e44c33f47bbf346dab01e67ab6f02::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite69e44c33f47bbf346dab01e67ab6f02::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInite69e44c33f47bbf346dab01e67ab6f02::$fallbackDirsPsr4;
            $loader->classMap = ComposerStaticInite69e44c33f47bbf346dab01e67ab6f02::$classMap;

        }, null, ClassLoader::class);
    }
}
