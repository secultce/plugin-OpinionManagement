<?php

namespace OpinionManagement\Controllers;

use MapasCulturais\Controller,
    MapasCulturais\App;

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

        $registration = $app->repo('Registration')->find($this->getData['id']);
        if(!$registration->canUser('viewUserEvaluation')) {
            $app->redirect($app->getBaseUrl());
        }

        $opinions = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration->id]);
        $this->json($opinions);
    }
}