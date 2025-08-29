<?php namespace Mercator\QrUploader\Models;

use Model;

class User extends Model
{
    use \Winter\Storm\Database\Traits\Hashable;

    public $table = 'mercator_qruploader_users';

    protected $hashable = ['password'];

    public $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'is_active'
    ];

    public $belongsToMany = [
        // IMPORTANT: Change 'UploadLink' to your actual model name
        'uploadlinks' => [
            'Mercator\QrUploader\Models\UploadLink',
            'table' => 'mercator_qruploader_uploadlinks_users',
            'key'      => 'user_id',
            'otherKey' => 'upload_link_id'
        ]
    ];
}
