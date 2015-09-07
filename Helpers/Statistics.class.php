<?php

/**
 * Statistics.class [Helper]
 *  Responsável pelas estatísticas, sessões e atualizações de tráfego do sistema. 
 *  Classe autocontida : :
 *  Basta inicializar a classe sem a necessidade de chamar seus métodos.
 * @copyright (c) 2015, Willian Monteiro
 */
class Statistics {

    /** Data dessas estatísticas */
    private $Date;

    /** Personalizar o tempo de sessão */
    private $Cache;

    /** Gerenciar o tráfego do site */
    private $Traffic;

    /** Descobrir e fazer a contagem de cada navegador que acessa o sistema */
    private $Browser;

    function __construct($Cache = null) {
        session_start();
        $this->CheckSession($Cache);
    }

    // Verifica e executa todos os métodos da classe!
    private function CheckSession($Cache = null) {
        $this->Date = date('Y-m-d');
        $this->Cache = ( (int) $Cache ? $Cache : 20 );

        if (empty($_SESSION['useronline'])):
            // Cria
            $this->setTraffic();
            $this->setSession();
            $this->CheckBrowser();
            $this->setUser();
            $this->updateBrowser();
        else:
            // Atualiza
            $this->updateTraffic();
            $this->updateSession();
            $this->CheckBrowser();
            $this->updateUser();
        endif;

        // Para que o sistema sempre check a sessão, quando o usuário entrar ou atualizar a página
        $this->Date = null;
    }

    /*
     * ************************************************************************************************
     *                     Sessão do usuário
     * ************************************************************************************************
     */

    // Inicia a sessão do usuário
    private function setSession() {
        $_SESSION['useronline'] = [
            "online_session" => session_id(),
            "online_startview" => date('Y-m-d H:i:s'),
            "online_endview" => date('Y-m-d H:i:s', strtotime("+{$this->Cache}minutes")),
            "online_ip" => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP),
            "online_url" => filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT),
            "online_agent" => filter_input(INPUT_SERVER, "HTTP_USER_AGENT", FILTER_DEFAULT)
        ];
    }

    // Atualiza sessão do usuário
    private function updateSession() {
        $_SESSION['useronline']['online_endview'] = date('Y-m-d H:i:s', strtotime("+{$this->Cache}minutes"));
        $_SESSION['useronline']['online_url'] = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
    }

    /*
     * ************************************************************************************************
     *                   Usuários / Visitas / Atualizações
     * ************************************************************************************************
     */

    // Verifica e insere o tráfego na tabela
    private function setTraffic() {
        $this->getTraffic();
        if (!$this->Traffic):
            // Se não obteve resultado a tabela está vazia, então criamos o primeiro resultado do dia
            $ArrSiteViews = ['siteviews_date' => $this->Date, 'siteviews_users' => 1, 'siteviews_views' => 1, 'siteviews_pages' => 1];
            $createSiteViews = new Create;
            $createSiteViews->ExeCreate('wm_siteviews', $ArrSiteViews);
        else:
            if (!$this->getCookie()):
                // Se não existe o usuário atualiza todos os campos
                $ArrSiteViews = ['siteviews_users' => $this->Traffic['siteviews_users'] + 1, 'siteviews_views' => $this->Traffic['siteviews_views'] + 1, 'siteviews_pages' => $this->Traffic['siteviews_pages'] + 1];
            else:
                // Se existe não é necessário atualizar a coluna de usuários
                $ArrSiteViews = ['siteviews_views' => $this->Traffic['siteviews_views'] + 1, 'siteviews_pages' => $this->Traffic['siteviews_pages'] + 1];
            endif;

            $updateSiteViews = new Update;
            $updateSiteViews->ExeUpdate('wm_siteviews', $ArrSiteViews, "WHERE siteviews_date = :date", "date={$this->Date}");

        endif;
    }

    // Verifica e atualiza os pageviews [Traffic Helper]
    private function updateTraffic() {
        $this->getTraffic();
        $ArrSiteViews = ['siteviews_pages' => $this->Traffic['siteviews_pages'] + 1];
        $updatePageViews = new Update;
        $updatePageViews->ExeUpdate('wm_siteviews', $ArrSiteViews, "WHERE siteviews_date = :date", "date={$this->Date}");

        // Limpa os dados da memória
        $this->Traffic = null;
    }

    // Obtém dados da tabela [Traffic Helper]
    private function getTraffic() {
        $readSiteViews = new Read;
        $readSiteViews->ExeRead('wm_siteviews', "WHERE siteviews_date = :date", "date={$this->Date}");
        if ($readSiteViews->getRowCount()):
            // Índice 0 para pegar apenas o resultado atual
            $this->Traffic = $readSiteViews->getResult()[0];
        endif;
    }

    // Verifica, cria e atualiza o cookie do usuário [Traffica Helper]
    private function getCookie() {
        $Cookie = filter_input(INPUT_COOKIE, 'useronline', FILTER_DEFAULT);
        // Cookie de um dia
        setcookie("useronline", base64_encode("sitename"), time() + 86400);
        if (!$Cookie):
            return false;
        else:
            return true;
        endif;
    }

    /*
     * ************************************************************************************************
     *                                                          Navegadores
     * ************************************************************************************************
     */

    // Identifica navegador do usuário
    private function CheckBrowser() {
        $this->Browser = $_SESSION['useronline']['online_agent'];
        if (strpos($this->Browser, 'Chrome')):
            $this->Browser = 'Chrome';
        elseif (strpos($this->Browser, 'Firefox')):
            $this->Browser = 'Firefox';
        elseif (strpos($this->Browser, 'MSIE') || strpos($this->Browser, 'Trident/')):
            $this->Browser = 'Internet Explorer';
        else:
            $this->Browser = 'Outros';
        endif;
    }

    // Atualiza tabela com dados dos navegadores
    private function updateBrowser() {
        $readAgent = new Read;
        $readAgent->ExeRead('wm_siteviews_agent', "WHERE agent_name = :agent", "agent={$this->Browser}");

        if (!$readAgent->getResult()):
            // Cria
            $ArrAgent = ['agent_name' => $this->Browser, 'agent_views' => 1];
            $createAgent = new Create;
            $createAgent->ExeCreate('wm_siteviews_agent', $ArrAgent);
        else:
            // Atualiza
            $ArrAgent = ['agent_views' => $readAgent->getResult()[0]['agent_views'] + 1];
            $updateAgent = new Update;
            $updateAgent->ExeUpdate('wm_siteviews_agent', $ArrAgent, "WHERE agent_name = :name", "name={$this->Browser}");
        endif;
    }

    /*
     * ************************************************************************************************
     *                                                          Usuários Online
     * ************************************************************************************************
     */

    // Cadastra usuario online na tabela
    private function setUser() {
        $onlineSession = $_SESSION['useronline'];
        $onlineSession['agent_name'] = $this->Browser;

        $createUser = new Create;
        $createUser->ExeCreate('wm_siteviews_online', $onlineSession);
    }

    // Atualiza navegação do usuário online
    private function updateUser() {
        $ArrOnline = [
            'online_endview' => $_SESSION['useronline']['online_endview'],
            'online_url' => $_SESSION['useronline']['online_url']
        ];

        $updateUser = new Update;
        $updateUser->ExeUpdate('wm_siteviews_online', $ArrOnline, "WHERE online_session = :ses", "ses={$_SESSION['useronline']['online_session']}");

        // Se o update nao for realizado com sucesso é por que a sessão foi morta então recria a sessão
        if (!$updateUser->getRowCount()):
            $readSession = new Read;
            $readSession->ExeRead('wm_siteviews_online', 'WHERE online_session = :onses', "onses={$_SESSION['useronline']['online_session']}");

            if (!$readSession->getRowCount()):
                // Reinicializa a sessão do usuário
                $this->setUser();
            endif;
        endif;
    }

}
