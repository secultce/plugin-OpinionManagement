<?php

namespace OpinionManagement\Controllers;

use MapasCulturais\Controller,
    MapasCulturais\App;
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
        $opinions = [];
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
            return;
        }

//        dump($opportunity->);
    }
}
