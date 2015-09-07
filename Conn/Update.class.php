<?php

/**
 * <b>Update.class [CRUD]</b>
 * Classe responsável por fazer alterações genéricas no bando de dados.
 * 
 * @copyright (c) 2015, Willian Monteiro
 */
class Update extends Conn {

    private $Table;
    private $Datas;
    private $Terms;
    private $Places;
    private $Result;

    /** @var PDOStatement <br>  Responsável pela query preparada da PDO  */
    private $Update;

    /** @var PDO <br> Armazena o objeto PDO retornado pela Conexão */
    private $Conn;

    public function ExeUpdate($Table, array $Datas, $Terms, $ParseString) {
        $this->Table = (string) $Table;
        $this->Datas = $Datas;
        $this->Terms = (string) $Terms;

        parse_str($ParseString, $this->Places);
        $this->getSyntax();
        $this->Execute();
    }

    public function getResult() {
        return $this->Result;
    }

    public function getRowCount() {
        return $this->Update->rowCount();
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
        $this->Update = $this->Conn->prepare($this->Update);
    }

    // Cria a sintaxe da query para Prepared Statements.
    private function getSyntax() {
        foreach ($this->Datas as $Key => $Value):
            $Places[] = $Key . ' = :' . $Key;
        endforeach;

        $Places = implode(', ', $Places);
        $this->Update = "UPDATE {$this->Table} SET {$Places} {$this->Terms}";
    }

    // Obtem a conexão e a sintaxe. Executa a query!
    private function Execute() {
        $this->Connect();
        try {
            $this->Update->execute(array_merge($this->Datas, $this->Places));
            $this->Result = true;
        } catch (PDOException $e) {
            $this->Result = null;
            WMErro("<b>Erro ao atualizar:</b> {$e->getMessage()}", $e->getCode());
        }
    }

}
