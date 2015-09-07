<?php

/**
 * <b>Read.class [CRUD]</b>
 * Classe responsável por leituras genéricas no bando de dados.
 * 
 * @copyright (c) 2015, Willian Monteiro
 */

class Read extends Conn {

    private $Select;
    private $Places;
    private $Result;

    /** @var PDOStatement <br>  Responsável pela query preparada da PDO  */
    private $Read;

    /** @var PDO <br> Armazena o objeto PDO retornado pela Conexão */
    private $Conn;

    /**
     * <b>Exe Read:</b> Executa uma leitura simplificada com Prepared Statments. Basta informar o nome da tabela,
     *  os termos da seleção e uma analize em cadeia (ParseString) para executar.
     * @param string $Table : : Nome da tabela
     * @param string $Terms : : WHERE | ORDER | LIMIT :limit | OFFSET :offset
     * @param string $ParseString : : link={$link}&link2={$link2}
     */
    public function ExeRead($Table, $Terms = null, $ParseString = null) {
        if (!empty($ParseString)):
            parse_str($ParseString, $this->Places);
        endif;

        $this->Select = "SELECT * FROM {$Table} {$Terms}";
        $this->Execute();
    }

    public function getResult() {
        return $this->Result;
    }

    /**
     * <b>Contar Registros: </b> Retorna o número de registros encontrados pelo select!
     * @return int  $Var = Quantidade de registros encontrados
     */
    public function getRowCount() {
        return $this->Read->rowCount();
    }
    
    /**
     * <b>Full Read:</b> Executa leitura de dados via query que deve ser montada manualmente para possibilitar
     * seleção de multiplas tabelas em uma única query!
     * @param string $Query : : Sintaxe da query
     * @param string $ParseString : : link={$link}&link2={$link2}
     */
    public function fullRead($Query, $ParseString = null) {
        $this->Select = (string) $Query;
        if (!empty($ParseString)):
            parse_str($ParseString, $this->Places);
        endif;
        $this->Execute();
    }
    
    public function setPlaces($ParseString) {
        parse_str($ParseString, $this->Places);
        $this->Execute();
    }

    /*     * * ********** Private Methods ************ */

    //  Obtem a conexão e prepara a query
    private function Connect() {
        $this->Conn = parent::getConn();
        $this->Read = $this->Conn->prepare($this->Select);
        $this->Read->setFetchMode(PDO::FETCH_ASSOC);
    }

    // Cria a sintaxe da query para Prepared Statements.
    private function getSyntax() {
        if ($this->Places):
            foreach ($this->Places as $Link => $Value):
                if ($Link == 'limit' || $Link == 'offset'):
                    $Value = (int) $Value;
                endif;
                $this->Read->bindValue(":{$Link}", $Value, (is_int($Value) ? PDO::PARAM_INT : PDO::PARAM_STR));
            endforeach;
        endif;
    }

    // Obtem a conexão e a sintaxe. Executa a query!
    private function Execute() {
        $this->Connect();
        try {
            $this->getSyntax();
            $this->Read->execute();
            $this->Result = $this->Read->fetchAll();
        } catch (PDOException $e) {
            $this->Result = null;
            WMErro("<b>Erro ao ler:</b> {$e->getMessage()}", $e->getCode());
        }
    }

}
