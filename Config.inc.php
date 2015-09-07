<?php

/* Configurações do Banco */
define('HOST', '');
define('USER', '');
define('PASS', '');
define('DB', '');

/*  Servidor de Email */
define('MAILUSER', '');
define('MAILPASS', '');
define('MAILPORT', '');
define('MAILHOST', '');

/* Definições do Site */
define('SITENAME', '');
define('SITEDESC', '');

/* Base do Site */
define('BASE', '');
define('THEME', '');

// Anexar e Inclur!
# imagens / css
define('INCLUDE_PATH', BASE . '/themes/' . THEME);
# Arquivos
define('REQUIRE_PATH', 'themes/' . THEME);
define('', '');

/* Auto Load de Classes */

function __autoload($Class) {
    $configDir = ['Conn', 'Helpers', 'Models'];
    $incDir = null;

    foreach ($configDir as $dirName):
        if (!$incDir && file_exists(__DIR__ . "\\{$dirName}\\{$Class}.class.php") && !is_dir(__DIR__ . "\\{$dirName}\\{$Class}.class.php")):
            include_once (__DIR__ . "\\{$dirName}\\{$Class}.class.php");
            $incDir = true;
        endif;
    endforeach;

    if (!$incDir):
        trigger_error("Não foi possível incluir {$Class}.class.php", E_USER_ERROR);
        die;
    endif;
}

/* Tratamento de erros */

// CSS Constantes :: Mensagens de Erro
define('WM_ACCEPT', 'accept');
define('WM_INFOR', 'infor');
define('WM_ALERT', 'alert');
define('WM_ERROR', 'error');

// WMErro :: Exibe erros lançados :: Front
function WMErro($ErrMsg, $ErrNum, $ErrDie = null) {
    $CssClass = ($ErrNum == E_USER_NOTICE ? WS_INFOR : ($ErrNum == E_USER_WARNING ? WS_ALERT : ($ErrNum == E_USER_ERROR ? WS_ERROR : $ErrNum)));
    echo "<p class=\"trigger {$CssClass}\">{$ErrMsg}<span class=\"ajax_close\"></span></p>";
    if ($ErrDie):
        die;
    endif;
}

//PHPErro :: Personaliza o gatilho PHP
function PHPErro($ErrNum, $ErrMsg, $ErrFile, $ErrLine) {
    $CssClass = ($ErrNum == E_USER_NOTICE ? WS_INFOR : ($ErrNum == E_USER_WARNING ? WS_ALERT : ($ErrNum == E_USER_ERROR ? WS_ERROR : $ErrNum)));
    echo "<p class=\"trigger {$CssClass}\">";
    echo "<b>Erro na linha {$ErrLine}  :: </b> $ErrMsg<br>";
    echo "<small>Em {$ErrFile}</small>";
    echo "<span class=\"ajax_close\"></span></p>";

    if ($ErrNum == E_USER_ERROR):
        die;
    endif;
}

set_error_handler('PHPErro');
