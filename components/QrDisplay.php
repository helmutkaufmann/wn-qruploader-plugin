<?php

namespace Mercator\QrUploader\Components;

use Cms\Classes\ComponentBase;
use Mercator\QrUploader\Models\UploadLink;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Url; // Add this line

class QrDisplay extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'QR Code Display',
            'description' => 'Displays a QR code for a specific upload link.'
        ];
    }

    public function defineProperties(): array
    {
        return [
            'shortCode' => [
                'title'       => 'Upload Link Short Code',
                'description' => 'The unique short code of the upload link to display.',
                'type'        => 'string',
                'required'    => true,
            ]
        ];
    }


    public function onRender(): void
{
        $link = UploadLink::where('short_code', $this->property('shortCode'))
            ->where('is_active', true)
            ->first();

        if (!$link) {
            $this->page['link'] = null;
            return;
        }

        $this->page['link'] = $link;
        $uploadUrl = \Url::to('/mercator/qruploader/upload/' . $link->short_code);
        $this->page['uploadUrl'] = $uploadUrl;

        try {
            $options = new QROptions([
                'version'      => QRCode::VERSION_AUTO,
                'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel'     => QRCode::ECC_H,
                'scale'        => 10,
                'imageBase64'  => true,
            ]);
            $this->page['qrCodeDataUri'] = (new QRCode($options))->render($uploadUrl);
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            $this->page['qrCodeDataUri'] = null;
        }
    }


    public function onRun(): void
    {
	return;
    }
}
