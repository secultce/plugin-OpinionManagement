<?php

namespace OpinionManagement;

use MapasCulturais\App,
    OpinionManagement\Controllers\OpinionManagement;

/**
 * @method part(string $string,  array $args = [])
 * @property mixed|null $controller
 */
class Plugin extends \MapasCulturais\Plugin
{

    public function _init(): void
    {
        $app = (new App)->i();

        $app->hook('template(<<registration|opportunity>>.<<single|view>>.head):begin', function () use ($app) {
            $app->view->enqueueScript(
                'app',
                'swal2',
                'js/sweetalert2.all.min.js'
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
        $app->hook('template(panel.<<registrations|index>>.head):begin', function () use ($app) {
            $app->view->enqueueScript(
                'app',
                'swal2',
                'js/sweetalert2.all.min.js'
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

        $app->hook('template(opportunity.single.registration-list-header):end', function() use($app) {
            $opportunity = $this->controller->requestedEntity;
            if($opportunity->evaluationMethodConfiguration->type != 'documentary') {
                return;
            }

            if($opportunity->canUser('@control')) {
                $this->part('OpinionManagement/admin-registrations-table-column.php');
                $app->view->enqueueScript(
                    'app',
                    'opinion-management-tab-registrations',
                    'OpinionManagement/js/admin-tab-registrations.js'
                );

                $app->hook('template(opportunity.single.registration-list-item):end', function() {
                    $this->part('OpinionManagement/admin-btn-show-opinion.php');
                });
            }
        });
        $app->hook('template(opportunity.single.user-registration-table--registration--status):end', function ($registration, $opportunity) use ($app) {
            if($opportunity->evaluationMethodConfiguration->type != 'documentary' || !$opportunity->publishedRegistrations) {
                return;
            }

            if($registration->canUser('@control')) {
                $this->part('OpinionManagement/user-btn-show-opinion.php', ['registration' => $registration]);
            }
        });

        $app->hook('template(registration.view.header-fieldset):after', function() use($app) {
            $registration = $this->controller->requestedEntity;
            $opportunity = $registration->opportunity;

            // http://localhost:8080/inscricao/612180872/

            if($opportunity->evaluationMethodConfiguration->type != 'documentary' || (!$opportunity->publishedRegistrations && !$opportunity->canUser('@control'))) {
                return;
            }

            if($registration->canUser('@control')) {
                $this->part('OpinionManagement/user-btn-show-opinion.php');
            }
        });


        $app->hook('template(panel.<<registrations|index>>.panel-registration):end', function ($registration) use ($app) {
            if(!$registration->opportunity->publishedRegistrations
                || $registration->opportunity->evaluationMethodConfiguration->type != 'documentary'
            ) return;
            $this->part('OpinionManagement/user-btn-show-opinion.php', ['registration' => $registration]);
            $app->view->enqueueScript(
                'app',
                'opinion-management',
                'OpinionManagement/js/opinionManagement.js'
            );
        });
    }

    /**
     * @throws \Exception
     */
    public function register(): void
    {
        $app = (new App)->i();

        $app->registerController('opinionManagement', OpinionManagement::class);

    }
}
