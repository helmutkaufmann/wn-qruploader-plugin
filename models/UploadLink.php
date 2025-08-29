<?php

namespace Mercator\QrUploader\Models; 

use Winter\Storm\Database\Model;
use Illuminate\Support\Str;

class UploadLink extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'mercator_qruploader_upload_links';

    protected $guarded = ['*'];

    protected $fillable = [
        'title',
        'description',
        'target_directory',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    public array $rules = [
        'title'            => 'required',
        'target_directory' => 'required',
        'start_date'       => 'required|date',
        'end_date'         => 'required|date|after_or_equal:start_date',
    ];

    public function beforeCreate(): void
    {
        $this->short_code = self::generateUniqueShortCode();
    }

    /**
     * This is the new mutator function.
     * It automatically handles the 'password' field from the form.
     */
    public function setPasswordAttribute(?string $value): void
    {
        // Only hash and set the password if a new one is provided.
        if (empty(trim($value))) {
            return;
        }

        $this->attributes['password_hash'] = password_hash($value, PASSWORD_DEFAULT);
    }

    public static function generateUniqueShortCode(int $length = 8): string
    {
        do {
            $code = Str::random($length);
        } while (self::where('short_code', $code)->exists());

        return $code;
    }

    public $belongsToMany = [
        'users' => [
            // This line will now work correctly
            'Mercator\QrUploader\Models\User',
            'table' => 'mercator_qruploader_uploadlinks_users',
            'key'      => 'upload_link_id',
            'otherKey' => 'user_id'
        ]
    ];
}
