<?php

namespace OpinionManagement\Controllers;

use MapasCulturais\Controller,
    MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Notification;
use OpinionManagement\Helpers\EvaluationList;

class OpinionManagement extends Controller
{
    public function GET_index(): void
    {
        $app = App::i();

        if(!$app->user->is('superAdmin')) {
            $this->layout = 'error-404';
            return;
        }

        $config = (object) [
            'autopublish' => true
        ];

        $this->render('index', ['config' => $config]);
    }

    public function GET_opinions(): void
    {
        $app = App::i();
        if($app->user->is('guest')) {
            $app->redirect($app->getBaseUrl());
        }

        /**
         * @var $registration \MapasCulturais\Entities\Registration
         */
        $registration = $app->repo('Registration')->find($this->getData['id']);
        if($registration->canUser('view')) {
            $opinions = new EvaluationList($registration);
            $this->json($opinions);
            return;
        }

        $this->errorJson(['permission-denied'], 403);
    }

    public function POST_publishOpinions(): void
    {
        $app = App::i();
        if($app->user->is('guest')) {
            $app->redirect($app->getBaseUrl());
        }

        $opportunity = $app->repo('Opportunity')->find($this->postData['id']);
        if(!$opportunity->isUserAdmin($app->user)) {
            $this->errorJson(['permission-denied'], 403);
            return;
        }


        $opportunity->setMetadata('publishedOpinions', 'true');
        $error = $opportunity->save(true);
        if($error) {
            $this->errorJson(['error' => new \PDOException('Cannot save this data')], 500);
            return;
        }

        $this->notificateUsers($opportunity->id);

        $this->json(['success' => true]);
    }

    public function notificateUsers(int $opportunityId, bool $verifyPublishingOpinions = true): bool
    {
        $app = App::i();
        $opportunity = $app->repo('Opportunity')->find($opportunityId);
        if($verifyPublishingOpinions && $opportunity->publishedOpinions === 'false') {
            return false;
        }

        $registrations = $app->repo('Registration')->findBy(['opportunity' => $opportunity]);
        foreach ($registrations as $registration) {
            $notification = new Notification();
            $notification->user = $registration->owner->user;
            $notification->message = "Sua inscrição <a style='font-weight:bold;' href='/inscricao/{$registration->id}'>{$registration->number}</a> da oportunidade <a style='font-weight:bold;' href='/oportunidade/{$opportunity->id}'/>{$opportunity->name}</a> está com os pareceres publicados.";
            $notification->save(true);
        }

        return true;
    }
}
