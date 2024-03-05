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
        // Load assets in head
        $app->hook('template(<<registration|opportunity|panel>>.<<single|view|registrations|index>>.head):begin', function () use ($app) {
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
                'OpinionManagement/css/opinionManagement.css'
            );
            $app->view->enqueueScript(
                'app',
                'opinion-management',
                'OpinionManagement/js/opinionManagement.js'
            );
        });

        $app->hook('template(opportunity.edit.registration-config):after', function () use ($app) {
            $opportunity = $this->controller->requestedEntity;
            /**
             * @todo: Refatorar quando for mudar para publicar pareceres técnicos
             */
            if($opportunity->evaluationMethodConfiguration->type != 'documentary') {
                return;
            }
            $this->part('OpinionManagement/selection-autopublish', ['opportunity' => $opportunity]);
        });

        $app->hook('template(opportunity.single.registration-list-header):end', function() use($app) {
            $opportunity = $this->controller->requestedEntity;
            /**
             * @todo: Refatorar quando for mudar para publicar pareceres técnicos
             */
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
            /**
             * @todo: Refatorar quando for mudar para publicar pareceres técnicos
             */
            if($opportunity->publishedOpinions != 'true') {
                return;
            }

            if($registration->canUser('@control')) {
                $this->part('OpinionManagement/user-btn-show-opinion.php', ['registration' => $registration]);
            }
        });

        $app->hook('template(opportunity.single.opportunity-registrations--tables):begin', function () use ($app) {
            $opportunity = $this->controller->requestedEntity;
            /**
             * @todo: Refatorar quando for mudar para publicar pareceres técnicos
             */
            if($opportunity->evaluationMethodConfiguration->type != 'documentary'
                || $opportunity->autopublishOpinions !== 'Não'
                || $opportunity->publishedOpinions == 'true'
            ) {
                return;
            }

            $this->part('OpinionManagement/admin-btn-publish-opinions.php', ['opportunity' => $opportunity]);
        });

        $app->hook('template(registration.view.header-fieldset):after', function() use($app) {
            $registration = $this->controller->requestedEntity;
            $opportunity = $registration->opportunity;

            /**
             * @todo: Refatorar quando for mudar para publicar pareceres técnicos
             */
            if($opportunity->evaluationMethodConfiguration->type != 'documentary' || (!$opportunity->publishedRegistrations && !$opportunity->canUser('@control'))) {
                return;
            }

            if($registration->canUser('@control')) {
                $this->part('OpinionManagement/user-btn-show-opinion.php');
            }
        });

        $app->hook('template(panel.<<registrations|index>>.panel-registration):end', function ($registration) use ($app) {
            /**
             * @todo: Refatorar quando for mudar para publicar pareceres técnicos
             */
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

        $app->hook('entity(Opportunity).publishRegistrations:before', function () {
            if($this->autopublishOpinions == 'Sim')
                $this->setMetadata('publishedOpinions', 'true');
        });
    }

    /**
     * @throws \Exception
     */
    public function register(): void
    {
        $app = (new App)->i();

        $app->registerController('opinionManagement', OpinionManagement::class);

        $this->registerOpportunityMetadata('autopublishOpinions', [
            'type' => 'select',
            'default_value' => 'Não',
            'label' => \MapasCulturais\i::__('Publicar pareceres automaticamente'),
            'description' => \MapasCulturais\i::__('Indica se os pareceres devem ser publicados automaticamente'),
            'options' => ['Sim', 'Não'],
            'required' => true,
        ]);
        $this->registerOpportunityMetadata('publishedOpinions', [
            'type' => 'select',
            'label' => \MapasCulturais\i::__('Pareceres publicados'),
            'default_value' => 'false',
            'options' => ['true', 'false'],
            'required' => true,
        ]);
    }
}
