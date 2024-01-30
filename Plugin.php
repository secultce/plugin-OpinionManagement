<?php

namespace OpinionManagement;

use MapasCulturais\App,
    MapasCulturais\Entities\OpportunityMeta,
    OpinionManagement\Controllers\OpinionManagement;

class Plugin extends \MapasCulturais\Plugin
{

    public function _init(): void
    {
        $app = App::i();

        $app->hook('template(<<registration|opportunity>>.single.head):begin', function () use ($app) {
            $app->view->enqueueScript(
                'app',
                'swal2',
                'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/swetalert2.all.min.js'
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
        $app->hook('template(panel.registrations.head):begin', function () use ($app) {
            $app->view->enqueueScript(
                'app',
                'swal2',
                'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/swetalert2.all.min.js'
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
            if($this->controller->requestedEntity->evaluationMethodConfiguration->type != 'documentary') {
                return;
            }

            if($app->user->is('admin')) {

                $this->part('OpinionManagement/admin-registrations-table-column.php');

                $app->hook('template(opportunity.single.registration-list-item):end', function() {
                    $this->part('OpinionManagement/admin-btn-show-opinion.php');
                });
            }
        });

        /*$app->hook('template(registration.view.header-fieldset):after', function() use($app) {
            $this->part('OpinionManagement/user-btn-show-opinion.php');
            $app->view->enqueueScript(
                'app',
                'opinion-management',
                'OpinionManagement/js/opinionManagement.js'
            );
        });*/

        $app->hook('template(opportunity.single.user-registration-table--registration--status):end', function ($reg_args) use ($app) {
            $registration = $reg_args;

            if($registration->canUser('@control')) {
                $this->part('OpinionManagement/user-btn-show-opinion.php', ['registration' => $registration]);
            }
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
            $opportunity = $this->controller->requestedEntity;
            if($opportunity->opinionsPublished) return;
            if($opportunity->isUserAdmin($app->user)) {
                $this->part('OpinionManagement/admin-btn-publish-opinion.php', ['entity' => $opportunity]);
            }
        });

        $app->hook('POST(opportunity.opinionsPublished)', function () use ($app) {

            $opportunity = $app->repo('Opportunity')->find($this->data['id']);
            if(!$opportunity->isUserAdmin($app->user)) return;

            $opinionsPublished = $app->repo('OpportunityMeta')->findOneBy([
                'owner' =>  $this->data['id'],
                'key' => 'opinionsPublished'
            ]);

            if(empty($opinionsPublished)) {
                $opinionsPublished = new OpportunityMeta;
                $opinionsPublished->owner = $opportunity;
                $opinionsPublished->key = 'opinionsPublished';
            }

            dump($opinionsPublished);

            $opinionsPublished->value = $this->data['opinionsPublished'];
            $error = $opinionsPublished->save(true);
            if($error !== null) {
                http_response_code(400);
                exit;
            }

            $message = $opinionsPublished->value ? 'Pareceres publicados' : 'Pareceres despublicados';
            $this->json(['message' => $message]);
        });
    }

    public function register(): void
    {
        $app = App::i();

        $app->registerController('opinionManagement', OpinionManagement::class);

        $this->registerOpportunityMetadata('opinionsPublished', [
            'label' => \MapasCulturais\i::__('Publicar Pareceres'),
            'type' => 'boolean'
        ]);

    }
}
