<?php

use function MapasCulturais\__exec;

return [
    'create table plugin_om_config' => function () {
        __exec('CREATE SEQUENCE plugin_om_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        __exec("CREATE TABLE plugin_om_config (
            id INT NOT NULL DEFAULT nextval('plugin_om_config_id_seq'),
            title TEXT NOT NULL,
            value TEXT NOT NULL,
            PRIMARY KEY(id)
        );");
    },
];