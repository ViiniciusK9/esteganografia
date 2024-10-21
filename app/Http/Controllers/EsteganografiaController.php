<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EsteganografiaController extends Controller
{

    public function index(): View
    {
        return view('esteganografia.index');
    }

    public function encode(Request $request)
    {
        $request->validate(
            [
                'file' => 'required|image|mimes:png',
                'text' => 'required',
            ],
            [
                'file.required' => 'A imagem é obrigatória',
                'text.required' => 'O texto é obrigatório',
                'file.image' => 'O arquivo precisa ser uma imagem',
                'file.mimes' => 'O arquivo precisa ser uma imagem PNG'
            ]
        );

        $image = $request->file('file');
        $fileName = $image->store();
        $text = $request->get('text');

        // Isso esta bem feio, mas está funcionando temporariamente =D
        $filePath = $this->getPublicStoragePath($fileName);

        $imgMod = $this->encodeMessage($filePath, $text);
        
        Storage::move($fileName, $fileName);
        
        return Storage::download($fileName);
    }

    public function decodeForm(): View
    {
        return view('esteganografia.decode-form');
    }

    public function decodeImage(Request $request)
    {
        $image = $request->file('file');
        $fileName = $image->store('imagens', 'public');
        $filePath = $this->getPublicStoragePath($fileName);
        $message = $this->decodeMessage($filePath);

        return view('esteganografia.decode-show', ['decodeMessage' => $message]);
    }

    private function getPublicStoragePath(string $fileName): string
    {
        return storage_path('/app/public/' . $fileName);
    }

    private function encodeMessage(string $fileName, string $message): void
    {
        $messageSize = strlen($message) + 4;
        $sizeEncoded = pack('N', $messageSize);
        $codedMessage = $sizeEncoded . $message;

        $image = imagecreatefrompng($fileName);

        $bits = '';
        foreach (str_split($codedMessage) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if (strlen($bits) > $width * $height) {
            return;
        }

        $newImage = imagecreatetruecolor($width, $height);

        $bitIndex = 0;

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {

                $pixel = imagecolorat($image, $i, $j);

                $red = ($pixel >> 16) & 0xFF;
                $green = ($pixel >> 8) & 0xFF;
                $blue = $pixel & 0xFF;

                if ($bitIndex < strlen($bits)) {
                    $newRed =   ($red   & 0xFE) | ($bits[$bitIndex] ?? '0');
                    $newGreen = ($green & 0xFE) | ($bits[$bitIndex + 1] ?? '0');
                    $newBlue =  ($blue  & 0xFE) | ($bits[$bitIndex + 2] ?? '0');
                    $bitIndex += 3;

                    $newPixel = ($newRed << 16) | ($newGreen << 8) | $newBlue;

                    imagesetpixel($newImage, $i, $j, $newPixel);

                    continue;
                }

                imagesetpixel($newImage, $i, $j, $pixel);
            }
        }

        imagepng($newImage, $fileName);
        return;
    }

    private function decodeMessage(string $filePath): string
    {
        $image = imagecreatefrompng($filePath);

        $width = imagesx($image);
        $height = imagesy($image);

        $messageSize = 0;

        for ($j = 0; $j < 11; $j++) {
            $pixel = imagecolorat($image, 0, $j);
            $red = ($pixel >> 16) & 0x01;
            $green = ($pixel >> 8) & 0x01;
            $blue = $pixel & 0x01;

            $messageSize = ($messageSize << 1) | $red;
            $messageSize = ($messageSize << 1) | $green;
            $messageSize = ($messageSize << 1) | $blue;
        }

        $messageSize = $messageSize >> 1;
        $byte = 0;
        $bitsCount = 0;
        $message = "";

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {

                if (strlen($message) >= $messageSize) {
                    break 2;
                }

                $pixel = imagecolorat($image, $i, $j);

                $red = ($pixel >> 16) & 0x01;
                $green = ($pixel >> 8) & 0x01;
                $blue = $pixel & 0x01;

                $byte = ($byte << 1) | $red;
                $byte = ($byte << 1) | $green;
                $byte = ($byte << 1) | $blue;
                $bitsCount += 3;

                if ($bitsCount >= 8) {
                    $offset = $bitsCount - 8;
                    $message .= chr($byte >> $offset);
                    $byte = $byte & (0xFF >> (8 - $offset));
                    $bitsCount -= 8;
                }
            }
        }

        return substr($message, 4);
    }
}
