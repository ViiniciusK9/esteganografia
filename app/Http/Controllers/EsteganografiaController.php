<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
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

        $img = $request->file('file');
        $fileName = $img->store();
        $text = $request->get('text');


        // Isso esta bem feio, mas está funcionando temporariamente =D
        $filePath = $this->getPublicStoragePath($fileName);

        $img_mod = $this->encodeMessage($filePath, $text);
        
        Storage::move($fileName, $fileName);
        
        return Storage::download($fileName);
    }

    public function decodeForm(): View
    {
        return view('esteganografia.decode-form');
    }

    public function decodeImage(Request $request)
    {
        $img = $request->file('file');
        $fileName = $img->store('imagens', 'public');
        $filePath = $this->getPublicStoragePath($fileName);
        $message = $this->decodeMessage($filePath);

        return view('esteganografia.decode-show', ['decodeMessage' => $message]);
    }

    private function getPublicStoragePath(string $filename): string
    {
        return storage_path('/app/public/' . $filename);
    }

    private function encodeMessage(string $filename, string $message): string
    {
        $mensagem = $message; 

        // Gera um novo texto adicionando o tamanho do mesmo no cabeçalho
        $tamanhoMensagem = strlen($mensagem) + 4;
        $tamanhoCodificado = pack('N', $tamanhoMensagem);
        $mensagemCodificada = $tamanhoCodificado . $mensagem;

        // Carrega a imagem na memória
        $im = imagecreatefrompng($filename);

        // Converte a mensagem em uma sequência de bits
        $bits = '';
        foreach (str_split($mensagemCodificada) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        // Recupera o tamanho da imagem
        $width = imagesx($im);
        $height = imagesy($im);

        // Verifica se a mensagem é maior do que a capacidade de armazenamento da imagem.
        // Caso positivo, encerra o programa.
        if (strlen($bits) > $width * $height) {
            return "O texto é maior do que a capacidade de armazenamento da imagem\n";
        }

        // Cria uma nova imagem de mesmo tamanho
        $new_im = imagecreatetruecolor($width, $height);

        // Variável de controle de acesso ao array da sequência de bits
        $bitIndex = 0;

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {

                // Recupera o pixel i,j da imagem 
                $pixel = imagecolorat($im, $i, $j);

                // Extrai os valores de vermelho, verde e azul do pixel
                $red = ($pixel >> 16) & 0xFF;
                $green = ($pixel >> 8) & 0xFF;
                $blue = $pixel & 0xFF;

                // Verifica se ainda exitem bits a serem armazenados
                if ($bitIndex < strlen($bits)) {

                    // Gera os novos valores de vermelho, verde e azul
                    $new_red =   ($red   & 0xFE) | ($bits[$bitIndex] ?? '0');
                    $new_green = ($green & 0xFE) | ($bits[$bitIndex + 1] ?? '0');
                    $new_blue =  ($blue  & 0xFE) | ($bits[$bitIndex + 2] ?? '0');
                    $bitIndex += 3;

                    // Gera um novo pixel com os valores das novas componentes de vermelho, verde e azul
                    $new_pixel = ($new_red << 16) | ($new_green << 8) | $new_blue;

                    // Atribui o novo pixel na imagem de saída
                    imagesetpixel($new_im, $i, $j, $new_pixel);

                    // Pula para a próxima iteração
                    continue;
                }

                // Atribui o pixel atual na imagem de saída
                imagesetpixel($new_im, $i, $j, $pixel);
            }
        }

        // Gera a imagem final com a mensagem codificada
        imagepng($new_im, $filename);
        //Storage::put($filename, $new_im);
        return "ok";
    }

    private function decodeMessage(string $filePath): string
    {
        // Carrega a imagem na memória
        $im = imagecreatefrompng($filePath);

        // Recupera o tamanho da imagem
        $width = imagesx($im);
        $height = imagesy($im);

        $tamanhoMensagem = 0;

        // Percorra os 11 primeiros pixels da imagem para extrair o tamanho do texto
        for ($j = 0; $j < 11; $j++) {
            $pixel = imagecolorat($im, 0, $j); // Suponha que os bytes do tamanho estejam nos primeiros 4 pixels na primeira linha da imagem
            $r = ($pixel >> 16) & 0x01;
            $g = ($pixel >> 8) & 0x01;
            $b = $pixel & 0x01;

            $tamanhoMensagem = ($tamanhoMensagem << 1) | $r;
            $tamanhoMensagem = ($tamanhoMensagem << 1) | $g;
            $tamanhoMensagem = ($tamanhoMensagem << 1) | $b;
        }

        // Como foi lido um bit a mais para computar o tamanho, o mesmo deve ser retirado
        $tamanhoMensagem = $tamanhoMensagem >> 1;

        // Variável que armazena o byte de texto
        $byte = 0;

        // Variável de contagem de bits lidos
        $bitsCount = 0;

        // Variável que armazena a mensagem
        $mensagem = "";

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {

                // Verifique se a mensagem já foi totalmente decodificada
                if (strlen($mensagem) >= $tamanhoMensagem) {
                    break 2; // Saia dos loops
                }

                // Recupera o pixel i,j da imagem 
                $pixel = imagecolorat($im, $i, $j);

                // Extrai os valores de vermelho, verde e azul do pixel
                $red = ($pixel >> 16) & 0x01;
                $green = ($pixel >> 8) & 0x01;
                $blue = $pixel & 0x01;

                // Armazena no byte os bits menos significativos das componentes do pixel
                $byte = ($byte << 1) | $red;
                $byte = ($byte << 1) | $green;
                $byte = ($byte << 1) | $blue;
                $bitsCount += 3;

                // Se temos mais de 8 bits lidos, extrair caractere
                if ($bitsCount >= 8) {

                    // Diferença de bits que excedeu a 8
                    $offset = $bitsCount - 8;

                    // Retira os bits excedentes da porção menos significativa e converte para caractere
                    $mensagem .= chr($byte >> $offset);

                    // Retira os bits já convertidos para caractere
                    $byte = $byte & (0xFF >> (8 - $offset));

                    // Ajusta o contador de bits retirando oito unidades
                    $bitsCount -= 8;
                }
            }
        }

        return substr($mensagem, 4);
    }
}
