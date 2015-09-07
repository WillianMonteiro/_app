<?php

/**
 * <b>Create.class [CRUD]</b>
 * Classe responsável por cadastros genéricos no bando de dados.
 * 
 * @copyright (c) 2015, Willian Monteiro
 */
class Create extends Conn {

    private $Table;
    private $Datas;
    private $Result;

    /** @var PDOStatement <br>  Responsável pela query preparada da PDO  */
    private $Create;

    /** @var PDO <br> Armazena o objeto PDOretornado pela Conexão*/
    private $Conn;

    /**
     * <b>ExeCreate:</b> Executa um cadastro simplificado no bando de dados utilizando prepared statements.
     *  Basta informar o nome da tabela e um array atribuitivo com nome da coluna e valor.
     * 
     * @param string $Table : : Informe o Nome da tabela no banco.
     * @param array $Datas : : Informe um array atribuitivo (Nome da coluna => Valor).
     */
    public function ExeCreate($Table, array $Datas) {
        $this->Table = (string) $Table;
        $this->Datas = $Datas;

        $this->getSyntax();
        $this->Execute();
    }

    /** 
     * <b>Obter resultado: </b> Retorna o ID do registro inserido no banco ou FALSE caso nenhum registro tenhas sido inserido
     * @return INT $variavel = lastInsertId or False 
     */
    public function getResult() {
        return $this->Result;
    }

    /*** ********** Private Methods *************/
   //  Obtem a conexão e prepara a query
    private function Connect() {
        $this->Conn = parent::getConn();
        $this->Create = $this->Conn->prepare($this->Create);
    }

    // Cria a sintaxe da query para Prepared Statements.
    private function getSyntax() {
        $Fields = implode(', ', array_keys($this->Datas));
        $Places = ':' . implode(', :', array_keys($this->Datas));
        $this->Create = "INSERT INTO {$this->Table} ({$Fields}) VALUES ({$Places})";
    }

  // Obtem a conexão e a sintaxe. Executa a query!
    private function Execute() {
        $this->Connect();
        try {
            $this->Create->execute($this->Datas);
            $this->Result = $this->Conn->lastInsertId();
        } catch (PDOException $e) {
            $this->Result = null;
            WMErro("<b>Erro ao cadastrar no banco de dados:</b> {$e->getMessage()}", $e->getCode());
        }
    }

}
