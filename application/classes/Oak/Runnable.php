<?php
    namespace Oak;

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Interface Runnable
     * Toda clase que el sistema de interpretacion de Oak use para ejecutar comandos ha de heredar de aqui
     * para poder marcar su metodo run correctamente
     *
     * @package Oak
     */
    interface Runnable
    {
        /**
         * Funcion a ejecutar para que la logica de este runnable funcione
         *
         * @param $telegram \Telegram Datos de la solicitud enviada por telegram al bot
         * @return void
         */
        function run($telegram);
    };