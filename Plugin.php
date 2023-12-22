<?php

namespace OpinionManagement;

use MapasCulturais\App;
use OpinionManagement\Controllers\OpinionManagement;

class Plugin extends \MapasCulturais\Plugin
{

    public function _init(): void
    {
        $app = App::i();

        $app->hook('template(opportunity.<<*>>):begin', function () use($app) {
           $app->view->enqueueScript(
               'app',
               'swal2',
               'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js'
           );
           $app->view->enqueueStyle(
               'app',
               'swal2-theme-secultce',
               'css/swal2-secultce.min.css'
           );
        });

        $app->hook('template(opportunity.<<create|edit|single>>.registration-list-header):end', function() use($app) {
            if($app->user->is('admin')) {

                $this->part('OpinionManagement/registrations-table-column-admin.php');

                $app->hook('template(opportunity.<<create|edit|single>>.registration-list-item):end', function() {
                    $this->part('OpinionManagement/btn-show-opinion.php');
                });

                $app->view->enqueueScript('app', 'opinion-management', 'OpinionManagement/js/opinionManagement.js');
            }
        });
    }

    public function register(): void
    {
        $app = App::i();

        $app->registerController('opinionManagement', OpinionManagement::class);

    }
}