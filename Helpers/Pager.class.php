<?php

/**
 * Pager.class [Helper]
 * Realiza a gestão e a paginação de resultados do sistema
 * @copyright (c) 2015, Willian Monteiro
 */
class Pager {

    /** Define o pager */
    private $Page;
    private $Limit;
    private $Offset;

    /** Realiza a leitura */
    private $Table;
    private $Terms;
    private $Places;

    /** Define o paginator */
    private $Rows;
    private $Link;
    private $MaxLinks;
    private $First;
    private $Last;

    /** Renderiza o paginator */
    private $Paginator;

    function __construct($Link, $First = null, $Last = null, $MaxLinks = null) {
        $this->Link = (string) $Link;
        $this->First = ( (string) $First ? $First : 'Primeira Página');
        $this->Last = ( (string) $Last ? $Last : 'Última Página');
        $this->MaxLinks = ( (int) $MaxLinks ? $MaxLinks : 5);
    }

    public function ExePager($Page, $Limit) {
        // Caso não exista a paginação o valor padrão será 1
        $this->Page = ( (int) $Page ? $Page : 1);
        $this->Limit = (int) $Limit;
        $this->Offset = ($this->Page * $this->Limit) - $this->Limit;
    }

    public function ReturnPage() {
        if ($this->Page > 1):
            $nPage = $this->Page - 1;
            header("Location: {$this->Link}{$nPage}");
        endif;
    }

    function getPage() {
        return $this->Page;
    }

    function getLimit() {
        return $this->Limit;
    }

    function getOffset() {
        return $this->Offset;
    }

    public function ExePaginator($Table, $Terms = null, $ParseString = null) {
        $this->Table = (string) $Table;
        $this->Terms = (string) $Terms;
        $this->Places = (string) $ParseString;
        $this->getSyntax();
    }

    public function getPaginator() {
        return $this->Paginator;
    }

    // Private methods

    private function getSyntax() {
        $read = new Read;
        $read->ExeRead($this->Table, $this->Terms, $this->Places);
        $this->Rows = $read->getRowCount();

        // Verifica se tem resultados para que se faça a paginação
        if ($this->Rows > $this->Limit):
            // Divide a quantidade de resultados (Rows) pelo limite para obter a quantidade de páginas
            $Paginas = ceil($this->Rows / $this->Limit);
            $MaxLinks = $this->MaxLinks;

            $this->Paginator = "<ul class=\"paginator\">";
            $this->Paginator .= "<li><a title=\"{$this->First}\" href=\"{$this->Link}1\">{$this->First}</a></li>";

            for ($iPag = $this->Page - $MaxLinks; $iPag <= $this->Page - 1; $iPag ++):
                if ($iPag >= 1):
                    $this->Paginator .= "<li><a title=\"Página {$iPag}\" href=\"{$this->Link}{$iPag}\">{$iPag}</a></li>";
                endif;
            endfor;

            $this->Paginator .= "<li><span class=\"active\">{$this->Page}</span></li>";

            for ($dPag = $this->Page + 1; $dPag <= $this->Page + $MaxLinks; $dPag ++):
                if ($dPag <= $Paginas):
                    $this->Paginator .= "<li><a title=\"Página {$dPag}\" href=\"{$this->Link}{$dPag}\">{$dPag}</a></li>";
                endif;
            endfor;

            $this->Paginator .= "<li><a title=\"{$this->Last}\" href=\"{$this->Link}{$Paginas}\">{$this->Last}</a></li>";
            $this->Paginator .= "</ul>";
        endif;
    }

}
