<?php

/**
 * Upload.class [Helper]
 * Responsável por executar Upload de imagens, arquivos e mídias no sistema.
 * @copyright (c) 2015, Willian Monteiro
 */
class Upload {

    private $File;
    private $Name;
    private $Send;

    /** Image Upload */
    private $Width;
    private $Image;

    /** Results */
    private $Result;
    private $Error;

    /** Diretórios */
    private $Folder;
    private static $BaseDir;

    /**
     * Cria o diretório padrão de uploads no sistema!<br>
     * <b>../uploads/</b>
     */
    function __construct($BaseDir = null) {
        self::$BaseDir = ( (string) $BaseDir ? $BaseDir : '../uploads/');
        // Se não existir e não for diretório
        if (!file_exists(self::$BaseDir) && !is_dir(self::$BaseDir)):
            // Cria a pasta com permissão máxima
            mkdir(self::$BaseDir, 0777);
        endif;
    }

    /**
     * <b>Enviar Imagem:</b> Basta envelopar um $_FILES de uma imagem e caso queira um nome e uma largura personalizada.
     * Caso não informe será aplicada uma largura padrão!
     * @param files $Image : : Enviar envelope de $_FILES (JPG, PNG ou GIF)
     * @param string $Name : : Nome da image ( ou do artigo )
     * @param int $Width : : Largura da imagem ( 1024 padrão )
     * @param string $Folder : : Pasta personalizada
     */
    public function Image(array $Image, $Name = null, $Width = null, $Folder = null) {
        $this->File = $Image;
        $this->Name = ( (string) $Name ? $Name : substr($Image['name'], 0, strrpos($Image['name'], '.')) );
        $this->Width = ((int) $Width ? $Width : 1024);
        $this->Folder = ((string) $Folder ? $Folder : 'images');

        $this->CheckFolder($this->Folder);
        $this->setFileName();
        $this->UploadImage();
    }

    /**
     * <b>Enviar Arquivo:</b> Basta envelopar um $_FILES de um arquivo e caso queira um nome e um tamanho personalizado.
     * Caso não informe o tamanho será 2mb!
     * @param files $File : : Enviar envelope de $_FILES (PDF ou DOCX)
     * @param string $Name : : Nome do arquivo ( ou do artigo )
     * @param string $Folder : : Pasta personalizada
     * @param string $MaxFileSize : : Tamanho máximo do arquivo (2mb)
     */
    public function File(array $File, $Name = null, $Folder = null, $MaxFileSize = null) {
        $this->File = $File;
        $this->Name = ( (string) $Name ? $Name : substr($File['name'], 0, strrpos($File['name'], '.')) );
        $this->Folder = ( (string) $Folder ? $Folder : 'files' );
        $MaxFileSize = ( (int) $MaxFileSize ? $MaxFileSize : 2 );

        // Extenções aceitas
        $FileAccept = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/pdf',
            'application/msword'
        ];

        // Valida o tamanho do arquivo
        // Transforma de byte para megabyte
        if ($this->File['size'] > ($MaxFileSize * (1024 * 1024))):
            // Caso seja maior que o permitido
            $this->Result = false;
            $this->Error = "Arquivo muito grande. O tamanho máximo permitido de {$MaxFileSize}mb";

        // Antes de fazer upload valida o tipo do arquivo
        elseif (!in_array($this->File['type'], $FileAccept)):
            // Caso seja um arquivo não permitido o sistema gera um erro
            $this->Result = false;
            $this->Error = 'Este tipo de arquivo não é aceito. Por favor envie arquivos com extensão .PDF, .DOC ou .DOCX!';
        else:
            // Verifica e cria pasta caso ela não exista
            $this->CheckFolder($this->Folder);
            // Verifica o nome
            $this->setFileName();
            // Move o arquivo
            $this->MoveFile();
        endif;
    }

    /**
     * <b>Enviar Mídia:</b> Basta envelopar um $_FILES de uma mídia e caso queira um nome e um tamanho personalizado.
     * Caso não informe o tamanho será 10mb!
     * @param files $Media : : Enviar envelope de $_FILES (MP3 ou MP4)
     * @param string $Name : : Nome do arquivo ( ou do artigo )
     * @param string $Folder : : Pasta personalizada
     * @param string $MaxFileSize : : Tamanho máximo do arquivo (40mb)
     */
    public function Media(array $Media, $Name = null, $Folder = null, $MaxFileSize = null) {
        $this->File = $Media;
        $this->Name = ( (string) $Name ? $Name : substr($Media['name'], 0, strrpos($Media['name'], '.')) );
        $this->Folder = ( (string) $Folder ? $Folder : 'medias' );
        $MaxFileSize = ( (int) $MaxFileSize ? $MaxFileSize : 10 );

        $FileAccept = [
            'audio/mp3',
            'video/mp4'
        ];

        if ($this->File['size'] > ($MaxFileSize * (1024 * 1024))):
            $this->Result = false;
            $this->Error = "Arquivo muito grande, tamanho máximo permitido de {$MaxFileSize}mb";
        elseif (!in_array($this->File['type'], $FileAccept)):
            $this->Result = false;
            $this->Error = 'Tipo de arquivo não suportado. Envie audio MP3 ou vídeo MP4!';
        else:
            $this->CheckFolder($this->Folder);
            $this->setFileName();
            $this->MoveFile();
        endif;
    }

    /**
     * <b>Verificar Upload:</b> Executando um getResult é possível verificar se o Upload foi executado  com sucesso ou não. 
     * Retorna uma string com o caminho e nome do arquivo ou FALSE.
     * @return string  : : Caminho e Nome do arquivo ou False
     */
    function getResult() {
        return $this->Result;
    }

    /**
     * <b>Obter Erro:</b> Retorna um array associativo com um code, um title, um erro e um tipo.
     * @return array $Error : : Array associatico com o erro
     */
    function getError() {
        return $this->Error;
    }

    // Private methods
    // Cria os diretórios com base em tipo de arquivo, ano e mês
    private function CheckFolder($Folder) {
        list($y, $m) = explode('/', date('Y/m_F'));
        $this->CreateFolder("{$Folder}");
        $this->CreateFolder("{$Folder}/{$y}");
        $this->CreateFolder("{$Folder}/{$y}/{$m}/");
        $this->Send = "{$Folder}/{$y}/{$m}/";
    }

    //Verifica e cria o diretório base
    private function CreateFolder($Folder) {
        if (!file_exists(self::$BaseDir . $Folder) && !is_dir(self::$BaseDir . $Folder)):
            mkdir(self::$BaseDir . $Folder, 0777);
        endif;
    }

    // Verifica e monta o nome dos arquivos tratando a string
    private function setFileName() {
        // Usando STRRCHR encontra a última ocorrência do ponto para obter a extenção do arquivo.
        $FileName = Check::Name($this->Name) . strrchr($this->File['name'], '.');

        // Verifica se o arquivo ja existe e renomeia
        if (file_exists(self::$BaseDir . $this->Send . $FileName)):
            $FileName = Check::Name($this->Name) . '-' . time() . strrchr($this->File['name'], '.');
        endif;

        $this->Name = $FileName;
    }

    // Realiza o upload de imagens redimensionando-as
    private function UploadImage() {
        // Valida o tipo de arquivo
        switch ($this->File['type']):
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
                $this->Image = imagecreatefromjpeg($this->File['tmp_name']);
                break;
            case 'image/png':
            case 'image/x-png':
                $this->Image = imagecreatefrompng($this->File['tmp_name']);
                break;
            case 'image/gif':
                $this->Image = imagecreatefromgif($this->File['tmp_name']);
                break;
        endswitch;

        if (!$this->Image):
            $this->Result = false;
            $this->Error = 'Tipo de arquivo inválido, envie imagens com a extensão JPG, PNG ou GIF!';
        else:
            // Pega o tamanho da imagem e valida se será necessário redimencioná-la
            $x = imagesx($this->Image);
            $y = imagesy($this->Image);
            $ImageX = ( $this->Width < $x ? $this->Width : $x );
            $ImageY = ($ImageX * $y) / $x;

            $NewImage = imagecreatetruecolor($ImageX, $ImageY);
            // Salva com fundo transparente
            imagealphablending($NewImage, false);
            imagesavealpha($NewImage, true);
            // Copia enviada para o servidor
            imagecopyresampled($NewImage, $this->Image, 0, 0, 0, 0, $ImageX, $ImageY, $x, $y);

            // Valida o nome da imagem
            switch ($this->File['type']):
                case 'image/jpg':
                case 'image/jpeg':
                case 'image/pjpeg':
                    imagejpeg($NewImage, self::$BaseDir . $this->Send . $this->Name);
                    break;
                case 'image/png':
                case 'image/x-png':
                    imagepng($NewImage, self::$BaseDir . $this->Send . $this->Name);
                    break;
                case 'image/gif':
                    imagegif($NewImage, self::$BaseDir . $this->Send . $this->Name);
                    break;
            endswitch;

            // Verifica se a imagem foi criada
            if (!$NewImage):
                $this->Result = false;
                $this->Error = 'Tipo de arquivo inválido, envie imagens JPG, PNG ou GIF';
            else:
                // Retorna o nome da imagem
                $this->Result = $this->Send . $this->Name;
                $this->Error = null;
            endif;

            // Os arquivos ja estão salvos na pasta, então limpa a memória
            imagedestroy($this->Image);
            imagedestroy($NewImage);

        endif;
    }

    // Envia arquivos e midias
    private function MoveFile() {
        // Verifica se o arquivo foi movido para uplodas / pasta-ano-mes / nome do arquivo
        if (move_uploaded_file($this->File['tmp_name'], self::$BaseDir . $this->Send . $this->Name)):
            // Se for true quer dizer que o arquivo foi enviado com sucesso
            $this->Result = $this->Send . $this->Name;
            $this->Error = null;
        else:
            $this->Result = false;
            $this->Error = 'Erro ao mover o arquivo.';
        endif;
    }

}
