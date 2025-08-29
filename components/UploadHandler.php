<?php

namespace Mercator\QrUploader\Components;

use Cms\Classes\ComponentBase;
use Mercator\QrUploader\Models\UploadLink;
use Carbon\Carbon;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\Validator;
use Winter\Storm\Support\Facades\Flash;
use Winter\Storm\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Log;

class UploadHandler extends ComponentBase
{
    public ?UploadLink $link;

    public function componentDetails(): array
    {
        return [
            'name'        => 'Upload Form Handler',
            'description' => 'Handles the entire image upload process.'
        ];
    }
    
    public function onRender(): void
    {
        Log::info("UploadHandler: onRender ..." . $this->param('short_code'));
        self::onRun();
    }

    public function onRun(): void
    {
        $shortCode = $this->param('short_code');
        $this->link = UploadLink::where('short_code', $shortCode)
            ->where('is_active', true)
            ->first();

        if (!$this->link) {
            $this->page['error'] = 'Invalid or expired upload link.';
            return;
        }

        $now = Carbon::now();
        if (!$now->between($this->link->start_date, $this->link->end_date)) {
            $this->page['error'] = sprintf(
                'The upload period is not active. You can upload between %s and %s.',
                $this->link->start_date->format('Y-m-d H:i'),
                $this->link->end_date->format('Y-m-d H:i')
            );
            return;
        }

        $requiresPassword = !empty($this->link->password_hash);
        $isAuthenticated = false;
        $authSessionKey = 'uploader_authed_' . $this->link->short_code;

        if ($requiresPassword) {
            if (Session::get($authSessionKey) === true) {
                $isAuthenticated = true;
            }
        } else {
            $isAuthenticated = true;
        }
        $this->page['isAuthenticated'] = $isAuthenticated;

        if ($isAuthenticated) {
            $this->loadGallery();
        }

        $this->page['link'] = $this->link;
        
        Log::info($this->page["link"]);
    }

    public function onAuthenticate()
    {
        $shortCode = $this->param('short_code');
        $this->link = UploadLink::where('short_code', $shortCode)->first();
        $password = post('password');

        if (!$this->link || empty($this->link->password_hash)) {
            throw new ApplicationException('Authentication failed.');
        }

        if (password_verify($password, $this->link->password_hash)) {
            Session::put('uploader_authed_' . $this->link->short_code, true);
            Flash::success('Password correct! You can now upload your images.');
            return Redirect::refresh();
        } else {
            Flash::error('Incorrect password.');
            return Redirect::refresh();
        }
    }

    public function onUpload()
    {
        $shortCode = $this->param('short_code');
        $this->link = UploadLink::where('short_code', $shortCode)->first();
    
        if (!$this->link) {
            throw new ApplicationException('Invalid request. Please scan the QR code again.');
        }
    
        $now = Carbon::now();
        if (!$now->between($this->link->start_date, $this->link->end_date)) {
            throw new ApplicationException('The upload period has just expired.');
        }
    
        $authSessionKey = 'uploader_authed_' . $this->link->short_code;
        if (!empty($this->link->password_hash) && Session::get($authSessionKey) !== true) {
            throw new ApplicationException('Authentication required.');
        }
    
        // --- Dynamic Validation Logic ---
        $allowedExtensions = 'jpg,jpeg,png,gif,webp,avif'; // Safe default for security
        if (!empty($this->link->allowed_extensions)) {
            // Sanitize the input: remove spaces, convert to lowercase
            $allowedExtensions = str_replace(' ', '', strtolower($this->link->allowed_extensions));
        }

        $rules = [
            'images.*' => "required|max:10240|mimes:{$allowedExtensions}"
        ];
        // --- End of Dynamic Validation Logic ---

        $validation = Validator::make(Input::all(), $rules);
    
        if ($validation->fails()) {
            throw new ApplicationException($validation->first());
        }
    
        $files = Input::file('images');
        $targetDir = 'media/' . $this->link->target_directory;
        $uploadedCount = 0;
    
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $safeName = preg_replace("/([^\w\s\d\-_~,;\[\]\(\).])|([\.]{2,})/", '', $originalName);
            $newName = uniqid() . '_' . $this->truncateFilename($safeName);
            $file->move(storage_path('app/' . $targetDir), $newName);
            $uploadedCount++;
        }
    
        if ($uploadedCount > 0) {
            Flash::success(sprintf('%d image(s) uploaded successfully!', $uploadedCount));
        }
    
        return ['redirectUrl' => Request::url()];
    }

    private function loadGallery(): void
    {
        $mediaDirectory = $this->link->target_directory;
        $fullPath = base_path(media_path($mediaDirectory));

        if (!File::isDirectory($fullPath)) {
            return;
        }

        $files = File::files($fullPath);
        usort($files, fn($a, $b) => $b->getMTime() <=> $a->getMTime());

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        $imageList = [];

        foreach (array_slice($files, 0, 50) as $file) {
            if (in_array(strtolower($file->getExtension()), $allowedExtensions)) {
                $imageList[] = [
                    'path'  => $mediaDirectory . '/' . $file->getFilename(),
                    'title' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                ];
            }
        }

        $this->page['latestImages'] = $imageList;
        $this->page['thumbOptions'] = [
            'width' => 300, 'height' => 300, 'options' => ['mode' => 'crop', 'quality' => 60, 'extension' => 'jpg']
        ];
        $this->page['imageOptions'] = [
            'width' => 1920, 'height' => 1080, 'options' => ['mode' => 'auto', 'quality' => 40, 'extension' => 'jpg']
        ];
    }

    private function truncateFilename(string $filename, int $maxLength = 64): string
    {
        $pathInfo = pathinfo($filename);
        $basename = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        if (mb_strlen($basename) > $maxLength) {
            $basename = mb_substr($basename, 0, $maxLength);
        }

        return $basename . $extension;
    }
}
