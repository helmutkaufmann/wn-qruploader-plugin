<?php namespace Mercator\QrUploader;

use System\Classes\PluginBase;
use Backend;

class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Qruploader',
            'description' => 'Generates unique, time-limited QR codes for image uploads.',
            'author'      => 'Mercator',
            'icon'        => 'icon-qrcode'
        ];
    }

    public function registerNavigation(): array
    {
        return [
            'qruploader' => [
                'label'       => 'QR Uploader',
                'url'         => Backend::url('mercator/qruploader/uploadlinks'),
                'icon'        => 'icon-qrcode',
                'permissions' => ['mercator.qruploader.*'],
                'order'       => 500,

                'sideMenu' => [
                    'uploadlinks' => [
                        'label'       => 'Upload Links',
                        'icon'        => 'icon-link',
                        'url'         => Backend::url('mercator/qruploader/uploadlinks'),
                        'permissions' => ['mercator.qruploader.access_links'],
                    ],
                    'users' => [
                        'label'       => 'Users',
                        'icon'        => 'icon-user',
                        'url'         => Backend::url('mercator/qruploader/users'),
                        'permissions' => ['mercator.qruploader.access_users'],
                    ],
                ]
            ],
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [
            'mercator.qruploader.access_links' => [
                'tab'   => 'QR Uploader',
                'label' => 'Access and manage QR Upload Links',
            ],
            'mercator.qruploader.access_users' => [
                'tab'   => 'QR Uploader',
                'label' => 'Access and manage associated users',
            ],
        ];
    }

    public function registerBlocks(): array
    {
        return [
            'qrcodedisplay' => '$/mercator/qruploader/blocks/qrcodedisplay.block',
        ];
    }

    public function registerComponents(): array
    {
        return [
            \Mercator\QrUploader\Components\QrDisplay::class   => 'qrDisplay',
            \Mercator\QrUploader\Components\UploadHandler::class => 'uploadHandler',
        ];
    }
}