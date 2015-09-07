<?php

/**
 * <b>Delete.class [CRUD]</b>
 * Classe responsável por fazer exclusões genéricas no bando de dados.
 * 
 * @copyright (c) 2015, Willian Monteiro
 */
class Delete extends Conn {

    private $Table;
    private $Terms;
    private $Places;
    private $Result;

    /** @var PDOStatement <br>  Responsável pela query preparada da PDO  */
    private $Delete;

    /** @var PDO <br> Armazena o objeto PDO retornado pela Conexão */
    private $Conn;

    public function ExeDelete($Table, $Terms, $ParseString) {
        $this->Table = (string) $Table;
        $this->Terms = (string) $Terms;
        
        parse_str($ParseString, $this->Places);
        $this->getSyntax();
        $this->Execute();
    }

    public function getResult() {
        return $this->Result;
    }

    public function getRowCount() {
        return $this->Delete->rowCount();
    }

    public function setPlaces($ParseString) {
        parse_str($ParseString, $this->Places);
        $this->getSyntax();
        $this->Execute();
    }

    /*     * * ********** Private Methods ************ */

    //  Obtem a conexão e prepara a query
    private function Connect() {
        $this->Conn = parent::getConn();
        $this->Delete = $this->Conn->prepare($this->Delete);
    }

    // Cria a sintaxe da query para Prepared Statements.
    private function getSyntax() {
        $this->Delete = "DELETE FROM {$this->Table} {$this->Terms}";
    }

    // Obtem a conexão e a sintaxe. Executa a query!
    private function Execute() {
        $this->Connect();
        try {
            $this->Delete->execute($this->Places);
            $this->Result = true;
        } catch (PDOException $e) {
            $this->Result = null;
            WMErro("<b>Erro ao deletar:</b> {$e->getMessage()}", $e->getCode());
        }
    }

}
