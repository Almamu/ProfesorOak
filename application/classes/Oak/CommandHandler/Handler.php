<?php
    namespace Oak\CommandHandler;

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Interface Handler base para todos los tipos de command handler
     *
     * @package Oak\CommandHandler
     */
    interface Handler
    {
        /**
         * Añade un comando a la lista de comandos disponibles en el sistema
         *
         * @param $command string El nombre del comando
         * @param $class string Clase que se encarga de ejecutar la logica del comando
         * @param $description string Descripcion del comando (para poder obtener su info en telegram por ejemplo)
         *
         * @return void
         */
        function registerHandler($command, $class, $description = "");

        /**
         * Comprueba si el mensaje recibido es un comando
         *
         * @param $telegram \Telegram Datos de la solicitud al bot enviados por Telegram
         * @return bool Indica si la solicitud es un comando o no
         */
        function isCommand($telegram);

        /**
         * Se encarga de interpretar y ejecutar un comando
         *
         * @param $telegram \Telegram Datos de la solicitud al bot enviados por Telegram
         * @return void
         */
        function run($telegram);
    };