<?php

if(!defined('ABSPATH')) exit;

// CRON: Borrar frases antiguas 
add_action('gs_cron_borrar_frases_antiguas', 'golden_shark_borrar_frases_antiguas');

function golden_shark_borrar_frases_antiguas(){
    $frases = golden_shark_get_frases();

    $limite = strtotime('-1 year');
    $filtradas = array_filter($frases, function($frase) use ($limite){ 
        if (preg_match('/^(\d{4}-\d{2}-\d{2})\|/', $frase, $matches)){
            return strtotime($matches[1]) >= $limite;
        }

        return true;
    });

    if(count($filtradas) < count($frases)){
        golden_shark_log('Se eliminaron frases antiguas por cron semanal');
        golden_shark_set_frases(array_values($filtradas));
    }
}