<?php

/**
 * View.class [Helper]
 * Responsável por carregar o template.
 * Arquitetura MVC
 * @copyright (c) 2015, Willian Monteiro
 */
class View {

    private $Data;

    /** @var $Keys Links para os templates */
    private $Keys;
    private $Values;

    /** @var string : : Carrega o template HTML  */
    private $Template;

    /**
     * <b>Carregar template view:</b> Informe o caminho e o nome do arquivo a ser carregado como view.
     *  Não é necessário informar extensão. O arquivo deve ter o formato view<b>.tpl.html</b>
     * @param string $Template : : Caminho / Nome do arquivo
     */
    public function Load($Template) {
        // Recebe como uma string
        $this->Template = INCLUDE_PATH . DIRECTORY_SEPARATOR . '_tpl' . DIRECTORY_SEPARATOR . (string) $Template;
        // Faz o carregamento do arquivo
        $this->Template = file_get_contents($this->Template . '.tpl.html');
        return $this->Template;
    }

    /**
     * <b>Exibir Template View:</b> Execute um foreach com um getResut() do seu model e informe o envelope
     *  neste método para configurar a view. É necessário carregar a vire acima do foreach com o método Load.
     * @param array $Data : : Array com os dados obtidos
     */
    public function Show(array $Data, $View) {
        $this->setKeys($Data);
        $this->setValues();
        $this->ShowView($View);
    }

    /**
     * <b>Carregar PHP View:</b> Tendo um arquivo PHP com echo em variáveis extraídas, utilize esse método
     * para incluir, povoar e exibir o mesmo. Basta informar o caminho do arquivo<b>.inc.php</b> e um
     * envelope de dados dentro de um foreach!
     * @param string $File : : Caminho / Nome do arquivo
     * @param array $Data : : Array com os dados obtidos
     */
    public function Request($File, array $Data) {
        extract($Data);
        require("{$File}.inc.php");
    }

    /* Private methods */

    //Executa o tratamento dos campos para substituição de chaves na view.
    private function setKeys($Data) {
        $this->Data = $Data;
        $this->Keys = explode('&', '#' . implode("#&#", array_keys($this->Data)) . '#');
    }

    //Obtém os valores a serem inseridos nas chaves da view.
    private function setValues() {
        $this->Values = array_values($this->Data);
    }

    //Exibe o template view com echo!
    private function ShowView($View) {
        $this->Template = $View;
        echo str_replace($this->Keys, $this->Values, $this->Template);
    }

}
