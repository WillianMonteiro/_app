<?php

/**
 * Check.class [Helper]
 * Classe responsável por manipular e validar dados do sistema.
 * 
 * @copyright (c) 2015, Willian Monteiro
 */
class Check {

    private static $Data;
    private static $Format;

    /**
     * <b>Valida Email:</b> Executa a validação do formato de E-mail.
     * @param string $Email : : Recebe uma conta de E-mail.
     * @return boolean : : Retorna true caso o E-mail informado seja valido, caso contrário false.
     */
    public static function Email($Email) {
        self::$Data = (string) $Email;
        // Expressão regular de E-mail
        self::$Format = '/[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\.\-]+\.[a-z]{2,4}$/';

        // Executa a validação
        if (preg_match(self::$Format, self::$Data)):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * <b>Transforma URL:</b> Transforma uma string no formato de URL amigável e retorna a string convertida!
     * @param string $Name : : Uma string qualquer
     * @return string $Data : : uma URL amigável válida
     */
    public static function Name($Name) {
        self::$Format = array();
        self::$Format['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        self::$Format['b'] = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

        self::$Data = strtr(utf8_decode($Name), utf8_decode(self::$Format['a']), self::$Format['b']);
        self::$Data = strip_tags(trim(self::$Data));
        self::$Data = str_replace(' ', '-', self::$Data);
        self::$Data = str_replace(array('-----', '----', '---', '--'), '-', self::$Data);

        return strtolower(utf8_encode(self::$Data));
    }

    /**
     * <b>Transforma Data:</b> Transforma uma data do formato dd/mm/yyyy em uma data no formato TIMESTAMP.
     * @param string $Date : : Data nos formatos (d/m/Y) ou (d/m/Y H:i:s).
     * @return string $Data : : Data no formato timestamp.
     */
    public static function Date($Date) {
        // Cria um array separando a data e a hora
        self::$Format = explode(' ', $Date);

        // Cria um array separando Dia, Mes, Ano
        self::$Data = explode('/', self::$Format[0]);

        // Se não for informado uma data, o sistema pega automaticamente o horário atual
        if (empty(self::$Format[1])):
            self::$Format[1] = date('H:i:s');
        endif;

        // Monta o formato TIMESTAMP
        self::$Data = self::$Data[2] . '-' . self::$Data[1] . '-' . self::$Data[0] . ' ' . self::$Format[1];
        return self::$Data;
    }

    /**
     * <b>Limita Palavras:</b> Limita a quantidade de palavras a serem exibidas em uma string!
     * @param string $String : Uma string qualquer
     * @param int $Limite : Limite de palavras a serem exibidas
     * @param string $Pointer : Pode ser nula. Usada para acrescentar algo após o corte da string
     * @return string $Result : String limitada pelo $Limite
     */
    public static function Words($String, $Limite, $Pointer = null) {
        self::$Data = strip_tags(trim($String));
        self::$Format = (int) $Limite;

        // Transforma a string em um array para saber quantas palavras a string possui
        $ArrWords = explode(' ', self::$Data);
        $NumWords = count($ArrWords);

        // Transforma o array em uma string delimitando o número de palavras
        $NewWords = implode(' ', array_slice($ArrWords, 0, self::$Format));

        // Se não for passado um paramêtro pointer é adicionado três pontos (...) ao final da string 
        $Pointer = (empty($Pointer) ? '...' : ' ' . $Pointer );

        // Se a string for menor que o $Limite ela não é cortada e nem é adicionado o $Pointer
        $Result = ( self::$Format < $NumWords ? $NewWords . $Pointer : self::$Data );
        return $Result;
    }

    /**
     * <b>Categoria por nome:</b> Este método é responsável por pesquisar o ID de uma categoria. 
     * Informe o nome da categoria e obtenha o ID da mesma
     * @param STRING $CategoryName : Nome da categoria.
     * @return INT category_id : ID da categoria informada.
     */
    public static function CategoryName($CategoryName) {
        $read = new Read;
        $read->ExeRead('wm_categories', "WHERE category_name = :name", "name={$CategoryName}");

        // Verifica se a pesquisa retornou algum resultado
        if ($read->getRowCount()):
            return $read->getResult()[0]['category_id'];
        else:
            echo "<b>Oppsss!</b> A categotia {$CategoryName} não foi encontrada!";
            die;
        endif;
    }

    /**
     * <b>Usuários Online: </b> Deleta os usuários que tem seu tempo de sessão expirado. Logo depois realiza uma leitura no banco de dados
     *  para obter o número de usuários online no sistema
     * @return INT : Número de usuários online
     */
    public static function OnlineUsers() {
        // Pega a data atual
        $now = date('Y-m-d H:i:s');
        // Deleta os usuários que estão com o tempo de sessão expirado
        $delExpiredSession = new Delete;
        $delExpiredSession->ExeDelete('wm_siteviews_online', "WHERE online_endview < :now", "now={$now}");

        // Le o banco de dados e retorna o número de usuários online
        $readOnlineUser = new Read;
        $readOnlineUser->ExeRead('wm_siteviews_online');
        return $readOnlineUser->getRowCount();
    }

    public static function CheckImage($ImageUrl, $ImageDesc, $ImageW = null, $ImageH = null) {
        // Caminho da Imagem
        self::$Data = $ImageUrl;

        // Verifica se a imagem existe e retorna ela redimensionada
        if (file_exists(self::$Data) && !is_dir(self::$Data)):
            $patch = BASE;
            $image = self::$Data;
            return "<img src=\"{$patch}/tim.php?src={$patch}/{$image}&w={$ImageW}&h={$ImageH}\" alt=\"{$ImageDesc}\" title=\"{$ImageDesc}\"/>";
        else:
            return false;
        endif;
    }

}
