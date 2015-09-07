<?php

/**
 * View.class [Helper]
 * Responsável por carregar o template.
 * Arquitetura MVC
 * @copyright (c) 2015, Willian Monteiro
 */


class View {
    private static $Data;
    
    /** @var $Keys Links para os templates */
    private static $Keys;
    
    private static $Values;
    
    /** @var string : : Carrega o template HTML  */
    private static $Template;
    
    /**
     * <b>Carregar template view:</b> Informe o caminho e o nome do arquivo a ser carregado como view.
     *  Não é necessário informar extensão. O arquivo deve ter o formato view<b>.tpl.html</b>
     * @param string $Template : : Caminho / Nome do arquivo
     */
    public static function Load($Template) {
        // Recebe como uma string
        self::$Template = (string) $Template;
        // Faz o carregamento do arquivo
        self::$Template = file_get_contents(self::$Template . '.tpl.html');
    }
    
    /**
     * <b>Exibir Template View:</b> Execute um foreach com um getResut() do seu model e informe o envelope
     *  neste método para configurar a view. É necessário carregar a vire acima do foreach com o método Load.
     * @param array $Data : : Array com os dados obtidos
     */
    public static function Show(array $Data) {
        self::setKeys($Data);
        self::setValues();
        self::ShowView();
    }
    
    /**
     * <b>Carregar PHP View:</b> Tendo um arquivo PHP com echo em variáveis extraídas, utilize esse método
     * para incluir, povoar e exibir o mesmo. Basta informar o caminho do arquivo<b>.inc.php</b> e um
     * envelope de dados dentro de um foreach!
     * @param string $File : : Caminho / Nome do arquivo
     * @param array $Data : : Array com os dados obtidos
     */
     public static function Request($File, array $Data) {
        extract($Data);
        require("{$File}.inc.php");
    }
    
    /* Private methods */
    
    //Executa o tratamento dos campos para substituição de chaves na view.
    private static function setKeys($Data) {
        self::$Data = $Data;
        self::$Keys = explode('&', '#' . implode("#&#", array_keys(self::$Data)) . '#');
    }

    //Obtém os valores a serem inseridos nas chaves da view.
    private static function setValues() {
        self::$Values = array_values(self::$Data);
    }

    //Exibe o template view com echo!
    private static function ShowView() {
        echo str_replace(self::$Keys, self::$Values, self::$Template);
    }

    
    
}
