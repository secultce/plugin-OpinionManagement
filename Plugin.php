<?php

namespace OpinionManagement;

use MapasCulturais\App;
use OpinionManagement\Controllers\OpinionManagement;

class Plugin extends \MapasCulturais\Plugin
{

    public function _init(): void
    {
        $app = App::i();



        $app->hook('template(<<registration|opportunity>>.<<*>>):begin', function () use ($app) {
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
            $app->view->enqueueStyle(
                'app',
                'opinionManagement',
                'css/opinionManagement.css'
            );

            $app->view->enqueueScript(
                'app',
                'opinion-management',
                'OpinionManagement/js/opinionManagement.js'
            );
        });

        $app->hook('template(panel.registrations.panel-header):before', function () use ($app) {
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
            $app->view->enqueueStyle(
                'app',
                'opinionManagement',
                'css/opinionManagement.css'
            );

            $app->view->enqueueScript(
                'app',
                'opinion-management',
                'OpinionManagement/js/opinionManagement.js'
            );
        });

        $app->hook('template(opportunity.<<create|edit|single>>.registration-list-header):end', function() use($app) {
            if($this->controller->requestedEntity->evaluationMethodConfiguration->type != 'documentary') {
                return;
            }

            if($app->user->is('admin')) {

                $this->part('OpinionManagement/registrations-table-column-admin.php');

                $app->hook('template(opportunity.<<create|edit|single>>.registration-list-item):end', function() {
                    $this->part('OpinionManagement/admin-btn-show-opinion.php');
                });

                $app->view->enqueueScript(
                    'app',
                    'opinion-management-tab-registrations',
                    'OpinionManagement/js/admin-tab-registrations.js'
                );
            }
        });

        $app->hook('template(registration.view.header-fieldset):after', function() use($app) {
            $this->part('OpinionManagement/user-btn-show-opinion.php');
            $app->view->enqueueScript(
                'app',
                'opinion-management',
                'OpinionManagement/js/opinionManagement.js'
            );
        });

        $app->hook('template(panel.registrations.panel-registration-meta):end', function ($registration) use ($app) {
            $this->part('OpinionManagement/user-btn-show-opinion.php', ['registration' => $registration]);
            $app->view->enqueueScript(
                'app',
                'opinion-management',
                'OpinionManagement/js/opinionManagement.js'
            );
        });

        $app->hook('template(opportunity.single.header-inscritos):actions', function () use ($app) {
            $this->part('OpinionManagement/admin-btn-publish-opinion.php');
        });
    }

    public function register(): void
    {
        $app = App::i();

        $app->registerController('opinionManagement', OpinionManagement::class);

    }
}